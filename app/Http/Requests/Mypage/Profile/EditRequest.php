<?php

namespace App\Http\Requests\Mypage\Profile;

use Illuminate\Foundation\Http\FormRequest;

class EditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    // リソースを操作する権限を持っているかを調べる処理
    // 今回は、ログインしているユーザの情報に対する編集なので、常にtrue
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
          // fileはファイルであることを検証する。
          // imageはアップロードされたファイルが画像（jpeg, png, bmp, gif, svg, webp）であることを検証する
          'avatar' => ['file', 'image'],
          'name' => ['required', 'string', 'max:255'],
        ];
    }
}
