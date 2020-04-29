@extends('layout.app')

@section('content')
<section class="content-header">
  <h1>
    @lang("messages.Create_Proposal_Budget")
  </h1>
</section>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      
      <div class="box">

        <form action="{{url('proposalbudget')}}" method="POST" id="createform" class="form-horizontal">
          {{ csrf_field() }}
          @include('proposal_budget.form')
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
        $('#submit').attr('disabled', true);
      });

    })
  </script>
  
@endsection