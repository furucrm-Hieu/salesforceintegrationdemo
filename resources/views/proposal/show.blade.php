@extends('layout.app')

@section('content')
<section class="content-header">
  <h1>
    @lang("messages.Detail_Proposal")
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
              <div class="col-sm-5" ></div>
              <div class="col-sm-1" >
                <a class="btn btn-block btn-primary" href="{{url('/proposal/'.$proposal->id.'/edit')}}">@lang("messages.Edit")</a>
              </div>
              <div class="col-sm-1" >
                <button type="button" class="btn btn-block btn-danger" onclick="getConfirmDelete(event)">@lang("messages.Delete")</button>
              </div>
              <div class="col-sm-5" ></div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">@lang("messages.Proposal_Name") : </label>
              <div class="col-sm-5" style="padding-top: 7px">
                {{$proposal->name}}
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">@lang("messages.Year") : </label>
              <div class="col-sm-5" style="padding-top: 7px">
                {{$proposal->year__c}}
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">@lang("messages.ProposalAt") : </label>
              <div class="col-sm-5" style="padding-top: 7px">
                {{ HelperDateTime::convertDateTimeUtcToJp($proposal->proposed_at__c) }}
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">@lang("messages.ApprovedAt") : </label>
              <div class="col-sm-5" style="padding-top: 7px">
                {{ HelperDateTime::convertDateTimeUtcToJp($proposal->approved_at__c) }}
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">@lang("messages.Total_Amount") : </label>
              <div class="col-sm-5" style="padding-top: 7px">
                {{ number_format($proposal->total_amount__c, 2)}}
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">@lang("messages.Detail") : </label>
              <div class="col-sm-5" style="padding-top: 7px">
                @if(!empty($proposal->details__c))
                  <textarea readonly class="form-control" rows="5">{{$proposal->details__c}}</textarea>
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

        <form id="delete-form" action="{{url('/proposal/'.$proposal->id)}}" method="POST" style="display: none;">
          @csrf
          <input name="_method" type="hidden" value="DELETE">
        </form>

      </div>

      <!--  -->
      <div class="box">
        <div class="box-header">
          <h3 class="box-title"><b>@lang("messages.Proposal_Budget")</b></h3>

        </div>
        <!-- /.box-header -->
        <div class="box-body">
          <div class="button-footer" style="height: 0px">
            <a class="btn btn-primary bt-center-dt" href="{{url('/proposalbudget/proposal-'.$proposal->id)}}">@lang("messages.Create_Proposal_Budget")</a>
          </div>
          <table class="table table-bordered table-striped" id="proposalBudget">
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
                <a href="{{ url('/proposalbudget/proposal-' . $value->id . '/edit') }}" title="@lang('messages.Edit')"><i class="fa fa-fw fa-edit"></i></a>
                <a href="javascript:void(0);" onclick="confirmDeleteAjax(event, 'proposalbudget', {{$value->id}})" title="@lang('messages.Delete')"><i class="fa fa-fw fa-trash-o"></i>
                </a>
              </td>
            </tr>
            @endforeach
            </tbody>           
          </table>
        </div>
        <!-- /.box-body -->
      </div>
      <!--  -->
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

  </script>
@endsection