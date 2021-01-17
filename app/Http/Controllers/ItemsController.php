<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;

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
}
