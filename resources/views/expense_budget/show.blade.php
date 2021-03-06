@extends('layout.app')

@section('content')
<section class="content-header">
  <h1>
    Detail Expense Budget
  </h1>
</section>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      
      <div class="box">

        <form method="POST" class="form-horizontal">
          
          <div class="box-body">

            <div class="form-group">
              <div class="col-sm-4" ></div>
              <div class="col-sm-3" >
                @if($expenseBudget->status_approve == HelperDateTime::PENDING)
                <button type="button" class="btn btn-info" onclick="postSubmitApproval(event)">Submit</button>
                <a class="btn btn-primary" href="{{url('/expense-budget/'.$expenseBudget->id.'/edit')}}">@lang("messages.Edit")</a>
                <button type="button" class="btn btn-danger" onclick="getConfirmDelete(event)">@lang("messages.Delete")</button>
                @endif
              </div>
              <div class="col-sm-5" ></div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">Expense Name : </label>
              <div class="col-sm-5" style="padding-top: 7px">
                {{$expenseBudget->expense->name}}
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">Budget Name : </label>
              <div class="col-sm-5" style="padding-top: 7px">
                {{$expenseBudget->budget->name}}
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">@lang("messages.Total_Amount") : </label>
              <div class="col-sm-5" style="padding-top: 7px">
                {{ number_format($expenseBudget->amount__c, 2)}}
              </div>
            </div>

            
            @if($errors->has('message'))
            <div class="form-group">
              <label class="col-sm-2 control-label"></label>
              <div class="col-sm-5" style="padding-top: 7px">
                <span class="red">{{ $errors->first('message') }}</span>
              </div>
            </div>
            @endif

          </div>
        </form>

        <form id="delete-form" action="{{url('/expense-budget/'.$expenseBudget->id)}}" method="POST" style="display: none;">
          @csrf
          <input name="_method" type="hidden" value="DELETE">
        </form>


        <form id="submitApproval-form" action="{{url('junctionEB-submit-approval')}}" method="POST" style="display: none;">
          {{ csrf_field() }}
          <input type="hidden" name="id" value="{{$expenseBudget->id}}" />
        </form>
      </div>


      <!-- start box approval processes -->
      @include('component.list_approval_processes')
      <!-- end box approval processes -->


    </div>

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
        $('#overlay').fadeIn();
        $('#delete-form').submit();
      }
    }

    function postSubmitApproval(event) {
      event.preventDefault();

      $('#overlay').fadeIn();
      $('#submitApproval-form').submit();

    }

  </script>
@endsection