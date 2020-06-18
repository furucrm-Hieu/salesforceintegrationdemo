@extends('layout.app')

@section('content')
<section class="content-header">
  <h1>
    @lang("messages.Proposal")
  </h1>
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      
      <div class="box">
        <!-- /.box-header -->
        <div class="box-body">
          <div class="button-footer" style="height: 0px">
            <a class="btn btn-primary bt-center-dt" href="{{route('proposal.create')}}">@lang("messages.Create_Proposal")</a>
          </div>
          <table id="proposal" class="table table-bordered table-striped">
            <thead>
            <tr>
              <th>@lang("messages.Proposal_Name")</th>
              <th>@lang("messages.ProposalAt")</th>
              <th>@lang("messages.ApprovedAt")</th>
              <th>@lang("messages.Year")</th>
              <th>@lang("messages.Total_Amount")</th>
              <th>@lang("messages.Action")</th>
            </tr>
            </thead>
            <tbody>
            @foreach($proposals as $value)
              <tr>
                <td><a href="{{ url('/proposal/' . $value->id ) }}">{{$value->name}}</a></td>
                <td>{{ HelperDateTime::convertDateTimeUtcToJp($value->proposed_at__c) }}</td>
                <td>{{ HelperDateTime::convertDateTimeUtcToJp($value->approved_at__c) }}</td>
                <td>{{ $value->year__c }}</td>
                <td>{{ number_format($value->total_amount__c, 2)}}</td>
                <td>
                  @if($value->status_approve == HelperDateTime::PENDING)
                  <a href="{{ url('/proposal/' . $value->id . '/edit') }}" title="@lang('messages.Edit')"><i class="fa fa-fw fa-edit"></i></a>
                  <a href="javascript:void(0);" onclick="confirmDeleteAjax(event, 'proposal', '{{$value->id}}') " title="@lang('messages.Delete')"><i class="fa fa-fw fa-trash-o"></i>
                  </a>
                  @endif
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection
@section('JS')
	<script>
	  $(function () {

	    $('#proposal').dataTable({
        "language" : dataLanguage,
      });
	  })

	</script>
@endsection
