
@extends('layout.detailLayout')

@section('name')
    @lang('messages.Budget_Detail')
@stop

@section('action')
    <div class="col-sm-5">
        <!-- <a class="btn btn-primary" href="{{url('/budget/'.$budget->id.'/edit')}}">@lang("messages.Edit")</a> -->
        <!-- <button type="button" class="btn btn-danger" onclick="getConfirmDelete(event)">@lang("messages.Delete")</button> -->
    </div>
@stop

@section('detailData')
    <div class="form-group">
        <label class="col-sm-2 control-label">@lang('messages.Name') : </label>
        <div class="col-sm-5" style="padding-top: 7px">
            {{ $budget->name }}
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">@lang('messages.Year') : </label>
        <div class="col-sm-5" style="padding-top: 7px">
            {{ $budget->year__c }}
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">@lang('messages.Total_Amount') : </label>
        <div class="col-sm-5" style="padding-top: 7px">
            {{ number_format($budget->total_amount__c, 2)}}
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
@stop

@section('otherData')

<form id="delete-form" action="{{url('/budget/'.$budget->id)}}" method="POST" style="display: none;">
    @csrf
    <input name="_method" type="hidden" value="DELETE">
</form>

<form id="submitApproval-form" action="{{url('budget-submit-approval')}}" method="POST" style="display: none;">
    {{ csrf_field() }}
    <input type="hidden" name="id" value="{{$budget->id}}" />
</form>

<!-- start box junction -->
<div class="box">
    <div class="box-header">
        <h3 class="box-title"><b>@lang("messages.Proposal_Budget")</b></h3>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="button-footer" style="height: 0px">
        </div>
        <table class="table table-bordered table-striped" id="proposalBudget">
            <thead>
                <tr>
                    <th>@lang('messages.Proposal_Name')</th>
                    <th>@lang('messages.Amount')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($proposal as $single)
                    <tr>
                        <td><a href="{{ url('/proposal/' . $single->proposal->id ) }}">{{$single->proposal->name}}</a></td>
                        <td>{{ number_format($single->amount__c, 2)}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<!-- end box junction -->

<!-- start box junction -->
<div class="box">
    <div class="box-header">
      <h3 class="box-title"><b>@lang('messages.Expense_Budget')</b></h3>
    </div>
    <div class="box-body">
      <div class="button-footer" style="height: 0px">
      </div>
      <table class="table table-bordered table-striped" id="expenseBudget">
        <thead>
        <tr>
          <th>@lang("messages.Budget_Name")</th>
          <th>@lang("messages.Amount")</th>
        </tr>
        </thead>
        <tbody>
        @foreach($expense as $value)
        <tr>
          <td><a href="{{ url('/expense/' . $value->expense->id ) }}">{{$value->expense->name}}</a></td>
          <td>{{ number_format($value->amount__c, 2)}}</td>
        </tr>
        @endforeach
        </tbody>
      </table>
    </div>
</div>
<!-- end box junction -->

@stop

@section('JS')
    <script>

        function getConfirmDelete(event) {
            event.preventDefault();

            var r = confirm(tranlateConfirm);
            if (r == true) {
                $('#overlay').fadeIn();
                $('#delete-form').submit();
            }
        }

        $(function () {
            $('#proposalBudget').dataTable({
                "language" : dataLanguage,
            });
            $('#expenseBudget').dataTable({
                "language" : dataLanguage,
            });
        })
    </script>
@endsection


