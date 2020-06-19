@extends('layout.app')

@section('content')
<section class="content-header">
  <h1>
    @lang('messages.Edit_Expense_Budget')
  </h1>
</section>

<section class="content">
  <div class="row">
    <div class="col-xs-12">

      <div class="box">

        <form action="{{url('expense-budget/'.$expenseBudget->id)}}" id="editform" method="POST" class="form-horizontal">
          {{ csrf_field() }}
          @method('PUT')
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

      $('#editform').submit(function() {
        $('#overlay').fadeIn();
        $('#submit').attr('disabled', true);
      });

    })

    function checkConnectSf() {
      if(!$('#editform')[0].checkValidity()) {
        $("#editform")[0].reportValidity();
      }
      else {
        alert(tokenError);
      }
    }
  </script>

@endsection
