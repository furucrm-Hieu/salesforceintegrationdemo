
@extends('layout.createLayout')

@section('name', empty($budget) ? __('messages.Create_Budget') : __('messages.Edit_Budget') )
@section('route', empty($budget) ? route('budget.store') : route('budget.update', ['budget' => $budget->id]))

@section('form')
<div class="box-body">
  <div class="form-group">
    <label class="col-sm-2 control-label" for="name">@lang('messages.Name') <span class="red"> *</span></label>
    <div class="col-sm-5">
      <input type="text" class="form-control" name="name" value="{{old('name', isset($budget->name) ? $budget->name : '')}}" required />
      @if ($errors->has('name'))
        <span class="red">{{ $errors->first('name') }}</span>
      @endif
    </div>
  </div>
  <div class="form-group">
    <label class="col-sm-2 control-label" for="year__c">@lang('messages.Year') <span class="red"> *</span></label>
    <div class="col-sm-5">
      <input type="number" class="form-control" min="1000" max="3000" name="year__c" value="{{old('year__c', isset($budget->year__c) ? $budget->year__c : '')}}" required />
      @if ($errors->has('year__c'))
        <span class="red">{{ $errors->first('year__c') }}</span>
      @endif
    </div>
  </div>

  @if($errors->has('message'))
  <div class="form-group">
    <label class="col-sm-2 control-label"></label>
    <div class="col-sm-5">
      <span class="red">{{ $errors->first('message') }}</span>
    </div>
  </div>
  @endif
</div>
  
<div class="box-footer button-footer">
  @if(!empty($budget))
    @method('put')
  @endif
  <input type="submit" id="submit" class="btn btn-info" value="@lang('messages.Submit')" />
  <a href="{{ isset($budget) ? url('budget/'.$budget->id) : url('budget')}}" class="btn btn-default">@lang("messages.Cancel")</a>
</div>
@stop
