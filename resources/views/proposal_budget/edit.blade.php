@extends('layout.app')

@section('content')
<section class="content-header">
  <h1>
    @lang("messages.Edit_Proposal_Budget")
  </h1>
</section>

<section class="content">
  <div class="row">
    <div class="col-xs-12">
      
      <div class="box">

        <form action="{{url('proposalbudget/'.$proposalBudget->id)}}" id="editform" method="POST" class="form-horizontal">
          {{ csrf_field() }}
          @method('PUT')
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

      $('#editform').submit(function() {
        $('#submit').attr('disabled', true);
      });

    })
  </script>
  
@endsection