@extends('layout.app')

@section('content')
<section class="content-header">
  <h1>
    @yield('name')
  </h1>
</section>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-body">
          <form method="POST" class="form-horizontal">
            <div class="form-group">
              <div class="col-sm-4" ></div>
                    @yield('action')
              <div class="col-sm-4" ></div>
            </div>
            @yield('detailData')
          </form>
        </div>
      </div>

      <!--  -->
      @yield('otherData')

  </div>
</section>

@endsection

