@extends('layout.app')

@section('content')
<section class="content-header">
  <h1>
    @lang('messages.Create_Expense_Budget_Title')
  </h1>
</section>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">

      <div class="box">

        <form action="{{url('expense-budget')}}" method="POST" id="createform" class="form-horizontal">
          {{ csrf_field() }}
          @include('expense_budget.form')
        </form>

      </div>
    </div>
  </div>
</section>

@endsection
@section('JS')
  <script>
    $(function () {

      $('#createform').submit(function() {
        $('#overlay').fadeIn();
        $('#submit').attr('disabled', true);
      });

    })

    function checkConnectSf() {
      if(!$('#createform')[0].checkValidity()) {
        $("#createform")[0].reportValidity();
      }
      else {
        alert(tokenError);
      }
    }
  </script>

@endsection
