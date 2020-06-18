@extends('layout.app')

@section('content')
<section class="content-header">
  <h1>
    @lang('messages.Budget')
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
            <a class="btn btn-primary bt-center-dt" href="{{ route('budget.create') }}">@lang('messages.Create_Budget')</a>
          </div>
          <table id="budgets" class="table table-bordered table-striped">
            <thead>
            <tr>
              <th>@lang('messages.Name')</th>
              <th>@lang('messages.Year')</th>
              <th>@lang('messages.Total_Amount')</th>
              <!-- <th>@lang('messages.Action')</th> -->
            </tr>
            </thead>
            <tbody>
              @foreach($budgets as $budget)
              <tr>
                <td><a href="{{ route('budget.show', ['budget'=>$budget->id]) }}" >{{ $budget->name }}</a></td>
                <td>{{ $budget->year__c }}</td>
                <td>{{ number_format($budget->total_amount__c, 2)}}</td>
                <!-- <td>
                    <a href="{{ url('/budget/' . $budget->id . '/edit') }}" title="@lang('messages.Edit')"><i class="fa fa-fw fa-edit"></i></a>
                    <a href="javascript:void(0);" onclick="confirmDeleteAjax(event, 'budget', {{$budget->id}}) " title="@lang('messages.Delete')"><i class="fa fa-fw fa-trash-o"></i>
                  </a>
                </td> -->
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

      $('#budgets').dataTable({
        "language" : dataLanguage,
      });

	  })
	</script>
@endsection
