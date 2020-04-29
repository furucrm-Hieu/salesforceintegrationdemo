
@extends('layout/app')

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

        <form action="@yield('route')" method="POST" id="budgetform" class="form-horizontal">
            @csrf
            @yield('form')      
        </form>

      </div>
    </div>
  </div>
</section>
@stop
@section('JS')
  <script>
    $(function () {

      $('#budgetform').submit(function() {
        $('#submit').attr('disabled', true);
      });

    })
  </script>
  
@endsection