@extends('layouts.app')

@section('title')
    商品一覧
@endsection

@section('content')
<div class="container">
  <div class="row">
      <!-- 検索フォームの記述 -->
      <form class="form-inline" method="GET" action="{{ route('top') }}" style="margin:auto;">
        <div class="input-group mt-3">
          <div class="input-group-prepend">
            <select class="custom-select" name="category" >
              <option value="">全て</option>
              @foreach ($categories as $category)
              <!-- foreachで回す事で、大カテゴリの項目が全て表示される -->
                <!-- 大カテゴリの実装は以下 -->
                <option value="primary:{{$category->id}}" class="font-weight-bold" {{ $defaults['category'] == "primary:" . $category->id ? 'selected' : ''}}>{{$category->name}}</option>
                @foreach ($category->secondaryCategories as $secondary)
                <!-- 小カテゴリの実装は以下 -->
                <!-- どちらの場合も、valueの値を[種別]:[ID]という書式にしている -->
                <!-- 種別が無い場合、idが重複するため大カテゴリなのか小カテゴリなのか判別がつかなくなる -->
                  <option value="secondary:{{$secondary->id}}" {{ $defaults['category'] == "secondary:" . $secondary->id ? 'selected' : ''}}>　{{$secondary->name}}</option>
                @endforeach
              @endforeach
            </select>
          </div>
          <input type="text" name="keyword" class="form-control" value="{{$defaults['keyword']}}" aria-label="Text input with dropdown button" placeholder="キーワード検索">
          <div class="input-group-append">
            <button type="submit" class="btn btn-outline-dark">
              <i class="fas fa-search"></i>
            </button>
          </div>
        </div>
      </form>
      <!-- 検索フォームの記述 -->
  </div>

  <div class="col-8 offset-2 mt-3">
      @if (session('status'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ session('status') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
      @endif
  </div>

    <div class="row mt-3">
      @foreach ($items as $item)
        <div class="col-sm-3 mb-3">
          <br>
          <div class="itemCard">
            <div class="position-relative overflow-hidden">
                <img class="card-img-top" src="data:image/png;base64,{{$item->image_file_name}}">
                <div class="position-absolute py-2 px-3" style="left: 0; bottom: 20px; color: white; background-color: rgba(0, 0, 0, 0.70)">
                    <i class="fas fa-yen-sign"></i>
                    <span class="ml-1">{{number_format($item->price)}}</span>
                </div>
                <!-- 「SOLD」ラベルを表示させる -->
                <!-- 商品($item)のisStateBoughtが真なら「SOLD」ラベルを表示 -->
                @if ($item->isStateBought)
                    <div class="position-absolute py-1 font-weight-bold d-flex justify-content-center align-items-end" style="left: 0; top: 0; color: white; background-color: #EA352C; transform: translate(-50%,-50%) rotate(-45deg); width: 125px; height: 125px; font-size: 20px;">
                        <span>SOLD</span>
                    </div>
                @endif
            </div>
            <div class="card-body">
                <small class="text-muted">{{$item->secondaryCategory->primaryCategory->name}} / {{$item->secondaryCategory->name}}</small>
                <h5 class="card-title">{{$item->name}}</h5>
            </div>
            <a href="{{ route('item', [$item->id]) }}" class="stretched-link"></a>
          </div>
        </div>
      @endforeach
    </div>
    <div class="d-flex justify-content-center">
    <!-- withQueryStringメソッドはlinksで出力されるリンクに現在のページのクエリストリングを付与 -->
      {{ $items->withQueryString()->links() }}
    </div>
</div>

<a href="{{route('sell')}}"
  class="bg-secondary text-white d-inline-block d-flex justify-content-center align-items-center flex-column"
  role="button"
  style="position: fixed; bottom: 30px; right: 30px; width: 150px; height: 150px; border-radius: 75px; z-index:1;"
>
    <div style="font-size: 24px;">出品</div>
    <div>
        <i class="fas fa-camera" style="font-size:30px;"></i>
    </div>
</a>
@endsection