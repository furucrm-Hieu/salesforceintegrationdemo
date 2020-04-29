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
              <div class="col-sm-5" ></div>
              <div class="col-sm-1" >
                <a class="btn btn-block btn-primary" href="@yield('routeEdit')">@lang("messages.Edit")</a>
              </div>
              <div class="col-sm-1" >
                <button type="button" class="btn btn-block btn-primary" onclick="getConfirmDelete(event)">@lang("messages.Delete")</button>
              </div>
              <div class="col-sm-5" ></div>
            </div>
            @yield('detailData')
          </form>
        </div>

        <form id="delete-form" action="{{url('/budget/'.$budget->id)}}" method="POST" style="display: none;">
          @csrf
          <input name="_method" type="hidden" value="DELETE">
        </form>
      </div>

      <!--  -->
      @yield('otherData')

  </div>
</section>

@endsection
@section('JS')
  <script>
    $(function () {

      $('#proposalBudget').dataTable({
        "language" : dataLanguage,
      });

    })
  </script>
  <script>
    
    function getConfirmDelete(event) {
      event.preventDefault();
      var r = confirm(tranlateConfirm);
      if (r == true) {
          $('#delete-form').submit();
      }
    }
  </script>
@endsection