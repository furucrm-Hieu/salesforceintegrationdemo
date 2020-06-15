@extends('layout.app')

@section('content')
<section class="content-header">
  <h1>
    Proposal Budget
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
            <a class="btn btn-primary bt-center-dt" href="{{route('proposal-budget.create')}}">New Proposal Budget</a>
          </div>
          <table id="proposal-budget" class="table table-bordered table-striped">
            <thead>
            <tr>
              <th>Proposal Name</th>
              <th>Budget Name</th>
              <th>Amount</th>
              <th>@lang("messages.Action")</th>
            </tr>
            </thead>
            <tbody>
            @foreach($list_proposal_budget as $value)
              <tr>
                <td><a href="{{ url('/proposal/' . $value->proposal->id ) }}">{{$value->proposal->name}}</a></td>
                <td><a href="{{ url('/budget/' . $value->budget->id ) }}">{{$value->budget->name}}</a></td>
                <td>{{ number_format($value->amount__c, 2)}}</td>
                <td>
                  <a href="{{ url('/proposal-budget/' . $value->id) }}" title="View"><i class="fa fa-fw fa-info-circle"></i></a>
                  @if($value->status_approve == false)
                  <a href="{{ url('/proposal-budget/' . $value->id . '/edit') }}" title="@lang('messages.Edit')"><i class="fa fa-fw fa-edit"></i></a>
                  <a href="javascript:void(0);" onclick="confirmDeleteAjax(event, 'expense-budget', '{{$value->id}}') " title="@lang('messages.Delete')"><i class="fa fa-fw fa-trash-o"></i>
                  </a>
                  @endif
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.box -->
    </div>
    <!-- /.col -->
  </div>
  <!-- /.row -->
</section>

@endsection
@section('JS')
	<script>
	  $(function () {

	    $('#proposal-budget').dataTable({
        "language" : dataLanguage,
      });
	  })

	</script>
@endsection
