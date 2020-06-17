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