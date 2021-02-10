@extends('layouts.app')

@section('title')
    {{$item->name}} | 商品詳細
@endsection

@section('content')
<div class="container">
    <div class="row border">
        <div class="mx-auto col col-12 col-sm-10 col-md-8 col-lg-8 col-xl-8">
            <div class="row mt-3">
                <div class="col-8 offset-2">
                    @if (session('message'))
                        <div class="alert alert-{{ session('type', 'success') }}" role="alert">
                            {{ session('message') }}
                        </div>
                    @endif
                </div>
            </div>

              @include('items.item_detail_panel', [
                  'item' => $item
              ])
            <div class="my-3">{!! nl2br(e($item->description)) !!}</div>
            <br>
            <div class="row">
                <div class="col-8 offset-2">
                <!-- ユーザーがログインしているかどうか -->
                @auth
                  <!-- ログインしていれば、idがseller_idと一致しているか -->
                  @if( Auth::id() !== $item->seller_id )
                    @if ($item->isStateSelling)
                      <a href="{{route('item.buy', [$item->id])}}" class="btn btn-secondary btn-block">購入</a>
                    @else
                      <button class="btn btn-dark btn-block" disabled>売却済み</button>
                    @endif
                  @else
                    <a href="{{route('item.edit', [$item->id])}}" class="btn btn-secondary btn-block">編集</a>
                  @endif
                @endauth
                </div>
            </div>
            <br>
        </div>
    </div>
</div>
@endsection