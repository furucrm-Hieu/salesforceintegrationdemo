@extends('layout.app')

@section('content')
<section class="content-header">
  <h1>
    Expense Budget
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
            <a class="btn btn-primary bt-center-dt" href="{{route('expense-budget.create')}}">New Expense Budget</a>
          </div>
          <table id="expense-budget" class="table table-bordered table-striped">
            <thead>
            <tr>
              <th>Expense Name</th>
              <th>Budget Name</th>
              <th>Amount</th>
              <th>@lang("messages.Action")</th>
            </tr>
            </thead>
            <tbody>
            @foreach($list_expense_budget as $value)
              <tr>
                <td><a href="{{ url('/expense/' . $value->expense->id ) }}">{{$value->expense->name}}</a></td>
                <td><a href="{{ url('/budget/' . $value->budget->id ) }}">{{$value->budget->name}}</a></td>
                <td>{{ number_format($value->amount__c, 2)}}</td>
                <td>
                  <a href="{{ url('/expense-budget/' . $value->id) }}" title="View"><i class="fa fa-fw fa-info-circle"></i></a>
                  @if($value->status_approve == false)
                  <a href="{{ url('/expense-budget/' . $value->id . '/edit') }}" title="@lang('messages.Edit')"><i class="fa fa-fw fa-edit"></i></a>
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

	    $('#expense-budget').dataTable({
        "language" : dataLanguage,
      });
	  })

	</script>
@endsection
