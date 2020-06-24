<div class="box">
  <div class="box-header">
    <h3 class="box-title"><b>@lang('messages.Approval_Process_Flow')</b></h3>
  </div>
  <div class="box-body">
    <table class="table table-bordered table-striped">
      <thead>
      <tr>
        <th>@lang('messages.Step_Name')</th>
        <th style="width: 300px">@lang('messages.Date')</th>
        <th style="width: 300px">@lang('messages.Status_TableList')</th>
        <th>@lang('messages.Assigned_To')</th>
        <th>Type Submit</th>
      </tr>
      </thead>
      <tbody>
      @foreach($listApprovalProcesses as $approval)
        <tr>
          <td>{{$approval['StepName']}}</td>
          <td>{{$approval['Date']}}</td>
          <td>{{$approval['Status']}}</td>
          <td>{{$approval['AssignedTo']}}</td>
          <td>{{$approval['TypeSubmit']}}</td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
