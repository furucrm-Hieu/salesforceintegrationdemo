@extends('layout.app')

@section('content')
<section class="content-header">
  <h1>
    @lang('messages.Detail_Expense')
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
                @if($expense->status_approve == HelperDateTime::PENDING)
                <button type="button" class="btn btn-info" onclick="postSubmitApproval(event)">@lang("messages.Submit_Payment")</button>
                <a class="btn btn-primary" href="{{url('/expense/'.$expense->id.'/edit')}}">@lang("messages.Edit")</a>
                <button type="button" class="btn btn-danger" onclick="getConfirmDelete(event)">@lang("messages.Delete")</button>
                @elseif($expense->status_approve == HelperDateTime::APPROVED)
                  @if(Auth::user()->roleName ==  HelperDateTime::FINANCE)
                    <button type="button" class="btn btn-info" onclick="postSubmitApproval(event)">
                      {{ ($expense->type_submit == true) ? __("messages.Submit_Payment") : __("messages.Submit_Request") }}
                    </button>
                  @endif
                @endif
              </div>
              <div class="col-sm-5" ></div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label"> @lang('messages.Expense_Name') : </label>
              <div class="col-sm-5" style="padding-top: 7px">
                {{$expense->name}}
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">@lang("messages.Year") : </label>
              <div class="col-sm-5" style="padding-top: 7px">
                {{$expense->year__c}}
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">@lang("messages.ProposalAt") : </label>
              <div class="col-sm-5" style="padding-top: 7px">
                {{ HelperDateTime::convertDateTimeUtcToJp($expense->proposed_at__c) }}
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">@lang("messages.ApprovedAt") : </label>
              <div class="col-sm-5" style="padding-top: 7px">
                {{ HelperDateTime::convertDateTimeUtcToJp($expense->approved_at__c) }}
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">@lang("messages.Total_Amount") : </label>
              <div class="col-sm-5" style="padding-top: 7px">
                {{ number_format($expense->total_amount__c, 2)}}
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">@lang("messages.Detail") : </label>
              <div class="col-sm-5" style="padding-top: 7px">
                @if(!empty($expense->details__c))
                  <textarea readonly class="form-control" rows="5">{{$expense->details__c}}</textarea>
                @endif
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

        <form id="delete-form" action="{{url('/expense/'.$expense->id)}}" method="POST" style="display: none;">
          @csrf
          <input name="_method" type="hidden" value="DELETE">
        </form>

        <form id="submitApproval-form" action="{{url('expense-submit-approval')}}" method="POST" style="display: none;">
          {{ csrf_field() }}
          <input type="hidden" name="id" value="{{$expense->id}}" />
        </form>
      </div>

      <!-- start box junction -->
      <div class="box">
        <div class="box-header">
          <h3 class="box-title"><b> @lang('messages.Expense_Budget')</b></h3>

        </div>
        <div class="box-body">
          <div class="button-footer" style="height: 0px">
            @if($expense->status_approve == HelperDateTime::PENDING)
            <a class="btn btn-primary bt-center-dt" href="{{url('/junctionEB/expense-'.$expense->id)}}">@lang('messages.Create_Expense_Budget_Button')</a>
            @elseif($expense->status_approve == HelperDateTime::APPROVED)
              @if(Auth::user()->roleName ==  HelperDateTime::FINANCE)
              <a class="btn btn-primary bt-center-dt" href="{{url('/junctionEB/expense-'.$expense->id)}}">@lang('messages.Create_Expense_Budget_Button')</a>
              @endif
            @endif
          </div>
          <table class="table table-bordered table-striped" id="expenseBudget">
            <thead>
            <tr>
              <th>@lang("messages.Budget_Name")</th>
              <th>@lang("messages.Amount")</th>
              <th style="width: 120px">@lang("messages.Action")</th>
            </tr>
            </thead>
            <tbody>
            @foreach($listBudget as $value)
            <tr>
              <td><a href="{{ url('/budget/' . $value->budget->id ) }}">{{$value->budget->name}}</a></td>
              <td>{{ number_format($value->amount__c, 2)}}</td>
              <td>
                @if($expense->status_approve == HelperDateTime::PENDING)
                <a href="{{ url('/expense-budget/' . $value->id . '/edit') }}" title="@lang('messages.Edit')"><i class="fa fa-fw fa-edit"></i></a>
                @elseif($expense->status_approve == HelperDateTime::APPROVED)
                  @if(Auth::user()->roleName ==  HelperDateTime::FINANCE)
                  <a href="{{ url('/expense-budget/' . $value->id . '/edit') }}" title="@lang('messages.Edit')"><i class="fa fa-fw fa-edit"></i></a>
                  <a href="javascript:void(0);" onclick="confirmDeleteAjax(event, 'expense-budget', '{{$value->id}}')" title="@lang('messages.Delete')"><i class="fa fa-fw fa-trash-o"></i>
                  </a>
                  @endif
                @endif
              </td>
            </tr>
            @endforeach
            </tbody>
          </table>
        </div>
      </div>
      <!-- end box junction -->

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

      $('#expenseBudget').dataTable({
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
