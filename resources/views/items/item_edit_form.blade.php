@extends('layouts.app')

@section('title')
    商品編集
@endsection

@section('content')
  <div class="container">
      <div class="row">
        <div class="col-8 offset-2">
          @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ session('status') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
            </div>
          @endif
        </div>
      </div>

      <div class="row">
        <div class="mx-auto col col-12 col-sm-11 col-md-9 col-lg-7 col-xl-6 border">
          <div class="font-weight-bold text-center border-bottom pb-3 pt-3" style="font-size: 24px">商品を編集する</div>
            <form method="POST" action="{{ route('item.edit',[$item->id]) }}" class="p-4" enctype="multipart/form-data">
              @csrf
                {{-- 商品画像 --}}
                <div>商品画像</div>
                  <div class="d-flex justify-content-center mt-2">
                    <span class="item-image-form image-picker">
                      <input type="file" name="item-image" class="d-none" accept="image/png,image/jpeg,image/gif" id="item-image" />
                        <label for="item-image" class="d-inline-block" role="button">
                          <img src="/storage/item-images/{{$item->image_file_name}}" style="object-fit: cover; width: 220px; height: 220px;">
                        </label>
                    </span>
                  </div>
                    @error('item-image')
                        <div style="color: #E4342E;" role="alert">
                            <strong>{{ $message }}</strong>
                        </div>
                    @enderror

                {{-- 商品名 --}}
                <div class="form-group mt-3">
                    <label for="name">商品名</label>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $item->name) }}" required autocomplete="name" autofocus>
                    @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                {{-- 商品の説明 --}}
                <div class="form-group mt-3">
                    <label for="description">商品の説明</label>
                    <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description" required autocomplete="description" autofocus>{{ $item->description }}</textarea>
                    @error('description')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                {{-- カテゴリ --}}
                <div class="form-group mt-3">
                    <label for="category">カテゴリ</label>
                    <select name="category" class="custom-select form-control @error('category') is-invalid @enderror">
                      @foreach ($categories as $category)
                      <!-- 大カテゴリの表示(薄いグレーで表示される部分) -->
                        <optgroup label="{{$category->name}}">
                          <!-- 大カテゴリに紐づく小カテゴリの一覧を取得 -->
                          @foreach($category->secondaryCategories as $secondary)
                              <!-- リストの中身 -->
                                <option value="{{$secondary->id}}" @if($item->secondaryCategory->id == $secondary->id) selected @endif>
                                <!-- リストの中身 -->
                                  {{$secondary->name}}
                                </option>
                          @endforeach
                        </optgroup>
                      @endforeach
                    </select>

                    @error('category')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                {{-- 商品の状態 --}}
                <div class="form-group mt-3">
                    <label for="condition">商品の状態</label>
                    <select name="condition" class="custom-select form-control @error('condition') is-invalid @enderror">
                        {{-- 次のパートで実装します --}}
                        @foreach ($conditions as $condition)
                        <!-- 前回選んだ商品状態を表示させる -->
                        <option value="{{$condition->id}}" @if($item->condition->name == $condition->name) selected @endif>
                          {{$condition->name}}
                        </option>
                      @endforeach
                    </select>
                    @error('condition')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                {{-- 販売価格 --}}
                <div class="form-group mt-3 mb-4">
                    <label for="price">販売価格</label>
                    <input id="price" type="number" class="form-control @error('price') is-invalid @enderror" name="price" value="{{ old('price', $item->price) }}" required autocomplete="price" autofocus>
                    @error('price')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
                  <button type="submit" class="btn btn-block btn-secondary">
                      更新する
                  </button>
                <div class="form-group mb-0 mt-3">
                    <a class="dropdown-item" data-toggle="modal" data-target="#modal-delete-{{ $item->id }}">
                      <button type="submit" class="btn btn-block btn-danger">
                          削除する
                      </button>
                    </a>
                </div>
            </form>

            <!-- modal -->
            <div id="modal-delete-{{ $item->id }}" class="modal fade" tabindex="-1" role="dialog">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="閉じる">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <form method="POST" action="{{ route('item.destroy', [$item->id]) }}">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                      商品を削除します。本当によろしいですか？
                    </div>
                    <div class="modal-footer justify-content-between">
                      <a class="btn btn-outline-grey" data-dismiss="modal">キャンセル</a>
                      <button type="submit" class="btn btn-danger">削除する</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <!-- modal -->
          </div>
        </div>
      </div>
  </div>
@endsection

