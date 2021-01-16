<?php

namespace App\Http\Controllers\MyPage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Mypage\Profile\EditRequest;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProfileController extends Controller
{
    //
    public function showProfileEditForm()
    {
      return view('mypage.profile_edit_form')
      // userという変数名でログインしているユーザの情報を渡している
          ->with('user', Auth::user());
    }

    public function editProfile(EditRequest $request)
    {
        $user = Auth::user();

        $user->name = $request->input('name');
        // inputタグのname属性に'name'が指定されているかどうかの確認
        if ($request->has('avatar')) {
          // アップロードされた画像の情報を取得
          $fileName = $this->saveAvatar($request->file('avatar'));
          $user->avatar_file_name = $fileName;
        }

        $user->save();

        return redirect()->back()
            ->with('status', 'プロフィールを変更しました。');
    }

    /**
      * アバター画像をリサイズして保存します
      *
      * @param UploadedFile $file アップロードされたアバター画像
      * @return string ファイル名
      */
    private function saveAvatar(UploadedFile $file): string
    {
      // makeTempPathメソッドは一時ファイルを生成してパスを取得する
        $tempPath = $this->makeTempPath();

      // Intervention Imageを使用して、画像をリサイズ後、一時ファイルに保存
        Image::make($file)->fit(200, 200)->save($tempPath);

      // Storageファサードを使用して画像をディスクに保存
      // 第一引数にはディスク名を指定

      // ① ローカルディスク（非公開）
      // ② ローカルディスク（公開）
      // ③ AWS S3
        $filePath = Storage::disk('public')
        // 第一引数はフォルダ名を指定
        // publicディスクではstorage/app/public/[指定したフォルダ名]に保存される

        // 第二引数は保存したい画像のFileインスタンスを指定
        // storage/app/public/avatarsフォルダに加工後の画像ファイルを保存
            ->putFile('avatars', new File($tempPath));

        return basename($filePath);
    }

    /**
      * 一時的なファイルを生成してパスを返します。
      *
      * @return string ファイルパス
      */
    private function makeTempPath(): string
    {
      // 一時ファイルを生成
      // /tmpに一時ファイルが生成され、そのファイルポインタが返される
        $tmp_fp = tmpfile();
      // メタ情報を取得
      // 返り値はメタ情報が格納された連想配列
        $meta = stream_get_meta_data($tmp_fp);
      // メタ情報からURI(ファイルのパス)を取得し返す
        return $meta["uri"];
    }
}
