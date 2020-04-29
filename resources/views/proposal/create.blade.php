@extends('layout.app')
@section('CSS')
<link rel="stylesheet" href="{{asset('template/bower_components/bootstrap-daterangepicker/daterangepicker.css')}}">
@endsection

@section('content')
<section class="content-header">
  <h1>
    @lang("messages.Create_Proposal")
  </h1>
</section>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      
      <div class="box">

        <form action="{{url('proposal')}}" method="POST" id="createform" class="form-horizontal">
          {{ csrf_field() }}
          @include('proposal.form')
        </form>

      </div>
    </div>
  </div>
</section>

@endsection
@section('JS')
  <script src="{{asset('template/bower_components/moment/min/moment.min.js')}}"></script>
  <script src="{{asset('template/bower_components/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
  <script>
    $(function () {

      $('#createform').submit(function() {
        $('#submit').attr('disabled', true);
      });
 
      $('#approved_at').daterangepicker({
        singleDatePicker: true,
        timePicker: true, 
        timePickerIncrement: 30, 
        locale: { format: 'YYYY-MM-DD HH:mm:ss' }
      });

      $('#proposed_at').daterangepicker({
        singleDatePicker: true,
        timePicker: true, 
        timePickerIncrement: 30, 
        locale: { format: 'YYYY-MM-DD HH:mm:ss' }
      });

    })
  </script>
  
@endsection