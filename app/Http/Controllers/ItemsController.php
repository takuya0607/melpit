<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\User;
use App\Models\PrimaryCategory;
use App\Models\ItemCondition;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Payjp\Charge;

class ItemsController extends Controller
{
    //
    public function showItems(Request $request)
    {

      $query = Item::query();

      // カテゴリで絞り込み

      // Requestインスタンスのfilledメソッドで、パラメータが指定されているかを調べることが可能
      // 第一引数にはパラメータの名前を指定→bladeのselectタグのname属性
      // categoryという名前のパラメータが指定されているかどうかを調べている

      // hasという似たメソッドがありますが、こちらは空文字列の場合もtrueを返す
      // キー名だけのパラメータが渡されてもtrueになってしまうため今回はfilledを使用
      if ($request->filled('category')) {
        // explodeメソッドで文字列を分割
        // 第一引数には、区切り文字(デリミタ)を指定→「：」で区切る事を認識させる
        // 第二引数には、分割する文字列を指定

        list($categoryType, $categoryID) = explode(':', $request->input('category'));

        // 例：[ 'secondary', '7' ]
        // この配列から値を取り出すのに、分割代入という文法を使用する事で下記のような処理になる
        // $categoryType = 'secondary'
        // $categoryID = '7'
        // $categoryTypeに種別が、$categoryIDにIDが入る

        if ($categoryType === 'primary') {
          // リレーション先のテーブルのカラムを基に絞り込む場合はwhereHasメソッドを使用
          // 第一引数にはリレーションを定義しているメソッドの名前を指定(Item.phpに記載)
          $query->whereHas('secondaryCategory', function ($query) use ($categoryID) {
          // 関数の中でリレーション先のテーブルに対する絞り込みを記述
            $query->where('primary_category_id', $categoryID);
          });
        } else if ($categoryType === 'secondary') {
          // 第一引数には、絞り込む対象のカラム名を指定
          // 第二引数には、絞り込む値を指定
            $query->where('secondary_category_id', $categoryID);
        }
      }

      // キーワードで絞り込み

      if ($request->filled('keyword')) {
        // キーワードを部分一致で検索するには、キーワードの前後を%で囲む必要がある
        // escapeメソッドは、特殊記号である%や_をエスケープ
        $keyword = '%' . $this->escape($request->input('keyword')) . '%';
        // カテゴリで検索をする処理
        $query->where(function ($query) use ($keyword) {
        // whereメソッドの第二引数にLIKEを指定することで、SQLのLIKE句を用いたパターンマッチング
            $query->where('name', 'LIKE', $keyword);
            $query->orWhere('description', 'LIKE', $keyword);
        });
      }

      // orderByRawメソッドを使って、出品中の商品を先に、購入済みの商品を後に表示
      // ORDER BY FIELD(state, 'selling', 'bought')の状態になる

      // FIELDはSQLの関数で、第一引数で指定した値が第二引数以降の何番目に該当するかを返す
      // stateがsellingの場合は1、boughtの場合は2を返す。
      // これを昇順で並べ替えることで、出品中(selling)の商品が先に、購入済み(bought)の商品が後になるようにソートされる
      $items = $query->orderByRaw( "FIELD(state, '" . Item::STATE_SELLING . "', '" . Item::STATE_BOUGHT . "')" )
          ->orderBy('id', 'DESC')
          ->paginate(52);

      return view('items.items')
          ->with('items', $items);
    }

    private function escape(string $value)
    {
      // LIKE句で使用できる特殊記号を置換して無効化（エスケープ）している
      // エスケープを行わない場合、%や_を含むキーワードを入力されると、意図しないパターンマッチングが実行される
        return str_replace(
            ['\\', '%', '_'],
            ['\\\\', '\\%', '\\_'],
            $value
        );
    }


    // Routeで指定した{item}がパラメータ($item)として渡される
    public function showItemDetail(Item $item)
    {
        return view('items.item_detail')
            ->with('item', $item);
    }

    public function showBuyItemForm(Item $item)
    {
      // 既に購入されている商品の商品購入画面にアクセスしてきた場合はHTTPステータスコード404(Not Found)を返す
        if (!$item->isStateSelling) {
            abort(404);
        }

        return view('items.item_buy_form')
            ->with('item', $item);
    }

    public function showItemEditForm(Item $item)
    {
      $categories = PrimaryCategory::query()
      ->with([
      // 連想配列のキー名としてEager Loadしたいリレーションの名前を指定
      // 正確にはEloquent Modelでリレーションを定義しているメソッドの名前
      'secondaryCategories' => function ($query) {
          $query->orderBy('sort_no');
        }
      ])
      ->orderBy('sort_no')
      ->get();
      // sort_noの昇順でitem_conditionsテーブルのデータを取得するクエリを組み立て
      $conditions = ItemCondition::orderBy('sort_no')->get();

      return view('items.item_edit_form')
          ->with('item', $item)
          ->with('categories', $categories)
          ->with('conditions', $conditions);
    }

    public function editItem(Request $request,Item $item)
    {
      // 商品画像の更新
        if ($request->has('item-image')) {
          // アップロードされた画像の情報を取得
          $fileName = $this->saveImage($request->file('item-image'));
          $item->image_file_name = $fileName;
        }

      // 商品名の更新
        $item->name = $request->input('name');
      // 商品説明の更新
        $item->description = $request->input('description');
      // カテゴリの更新
        $item->secondary_category_id = $request->input('category');
      // 商品状態の更新
        $item->item_condition_id = $request->input('condition');
      // 商品価格の更新
        $item->price = $request->input('price');

        $item->save();

        return redirect('/')
        ->with('status', '商品情報を更新しました。');
    }

    private function saveImage(UploadedFile $file): string
    {
      // makeTempPathメソッドは一時ファイルを生成してパスを取得する(下で定義済み)
      $tempPath = $this->makeTempPath();

      // Intervention Imageを使用して、画像をリサイズ後、一時ファイルに保存
      Image::make($file)->fit(300, 300)->save($tempPath);

      // ファイルの保存場所を指定
      $filePath = Storage::disk('public')
          ->putFile('item-images', new File($tempPath));

      return basename($filePath);
    }

    /**
    * 一時的なファイルを生成してパスを返します。
    *
    * @return string ファイルパス
    */
    private function makeTempPath(): string
    {
      $tmp_fp = tmpfile();
      $meta   = stream_get_meta_data($tmp_fp);
      return $meta["uri"];
    }


    public function buyItem(Request $request, Item $item)
    {
        $user = Auth::user();

        if (!$item->isStateSelling) {
            abort(404);
        }

        $token = $request->input('card-token');

        // try ~ catch
        //  例外処理の際に使用するメソッドで、例外の場合catchの内容が実行される
        try {
            $this->settlement($item->id, $item->seller->id, $user->id, $token);
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()
                ->with('type', 'danger')
                ->with('message', '購入処理が失敗しました。');
        }

        return redirect()->route('item', [$item->id])
            ->with('message', '商品を購入しました。');
    }

    private function settlement($itemID, $sellerID, $buyerID, $token)
    {
      // DBファサードのbeginTransactionでトランザクション(整合性の確認)を開始できる
        DB::beginTransaction();

        try {
          // 多重決済を避ける記述
            // findはidを指定して単一のレコードを取得するメソッド
            // lockForUpdateメソッドと組み合わせることで単一のレコードを排他ロックしつつ取得
            // トランザクションをコミットまたはロールバックするとロックは自動的に解除される
            $seller = User::lockForUpdate()->find($sellerID);
            $item = Item::lockForUpdate()->find($itemID);

            if ($item->isStateBought) {
                throw new \Exception('多重決済');
            }

            $item->state = Item::STATE_BOUGHT;
            $item->bought_at = Carbon::now();
            $item->buyer_id = $buyerID;
            $item->save();

            // PAY.JPにカードトークンを送信し、決済を行う
            $charge = Charge::create([
              	// カードトークンの指定
                'card' => $token,
                // 金額の指定
                'amount' => $item->price,
                // 通貨の指定
                'currency' => 'jpy'
            ]);
            // 上記で取得したChargeインスタンスのcapturedフィールドで判別
            // 支払いが正常に処理されなかったら、catchの処理が実行される
            if (!$charge->captured) {
                throw new \Exception('支払い確定失敗');
            }

            // 売った金額は＋で積み上げ方式
            $seller->sales += $item->price;
            $seller->save();
          // ここから例外処理の記述
        } catch (\Exception $e) {
          // rollbackメソッドでトランザクションをロールバック(変更を取り消す)
            DB::rollBack();
            throw $e;
        }
        // トランザクションをコミット（確定）する
        DB::commit();
    }

    public function destroy(Item $item)
    {
      $item->delete();
      return redirect('/')
      ->with('status', '商品情報を削除しました。');
    }
}
