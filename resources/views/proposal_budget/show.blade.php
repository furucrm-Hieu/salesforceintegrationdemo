@extends('layout.app')

@section('content')
<section class="content-header">
  <h1>
    Detail Proposal Budget
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
                @if($proposalBudget->status_approve == false)
                <button type="button" class="btn btn-info" onclick="postSubmitApproval(event)">Submit</button>
                <a class="btn btn-primary" href="{{url('/proposal-budget/'.$proposalBudget->id.'/edit')}}">@lang("messages.Edit")</a>
                <button type="button" class="btn btn-danger" onclick="getConfirmDelete(event)">@lang("messages.Delete")</button>
                @endif
              </div>
              <div class="col-sm-5" ></div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">Expense Name : </label>
              <div class="col-sm-5" style="padding-top: 7px">
                {{$proposalBudget->proposal->name}}
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">Budget Name : </label>
              <div class="col-sm-5" style="padding-top: 7px">
                {{$proposalBudget->budget->name}}
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">@lang("messages.Total_Amount") : </label>
              <div class="col-sm-5" style="padding-top: 7px">
                {{ number_format($proposalBudget->amount__c, 2)}}
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

        <form id="delete-form" action="{{url('/proposal-budget/'.$proposalBudget->id)}}" method="POST" style="display: none;">
          @csrf
          <input name="_method" type="hidden" value="DELETE">
        </form>


        <form id="submitApproval-form" action="{{url('junctionPB-submit-approval')}}" method="POST" style="display: none;">
          {{ csrf_field() }}
          <input type="hidden" name="id" value="{{$proposalBudget->id}}" />
        </form>
      </div>


      <!-- start box approval processes -->
      <div class="box">
        <div class="box-header">
          <h3 class="box-title"><b>Approval Process Flow</b></h3>
        </div>
        <div class="box-body">
          <table class="table table-bordered table-striped">
            <thead>
            <tr>
              <th>Step Name</th>
              <th style="width: 300px">Date</th>
              <th style="width: 300px">Status</th>
              <th>Assigned To</th>
            </tr>
            </thead>
            <tbody>
            @foreach($listApprovalProcesses as $approval)
              <tr>
                <td>{{$approval['StepName']}}</td>
                <td>{{$approval['Date']}}</td>
                <td>{{$approval['Status']}}</td>
                <td>{{$approval['AssignedTo']}}</td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
      </div>
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