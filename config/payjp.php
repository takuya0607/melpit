<?php

// env関数で.envまたは環境変数の値を取得
return [
    'public_key' => env('PAYJP_PUBLIC_KEY'),
    'secret_key' => env('PAYJP_SECRET_KEY'),
];