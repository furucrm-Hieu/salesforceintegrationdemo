<div class="box-body">

  <input type="hidden" name="linkRedirect" value="{{isset($linkRedirect) ? $linkRedirect : ''}}">

  <div class="form-group">
    <label class="col-sm-2 control-label">@lang('messages.Expense')<span class="red"> *</span> </label>
    <select class="form-control custom-select" disabled>
      <option value=""> -- @lang("messages.None") --</option>
      @foreach($expenses as $key => $value)
        <option value="{{$key}}" {{ ($key == $expenseBudget->expense__c) ? 'selected' : '' }}>{{$value}}</option>
      @endforeach
    </select>

    <input type="hidden" name="expense__c" value="{{$expenseBudget->expense__c}}">

  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">@lang("messages.Budget")<span class="red"> *</span></label>
    <select class="form-control custom-select" name="budget__c"  {{($type=='edit') ? 'disabled' : 'required'}}>
      <option value=""> -- @lang("messages.None") --</option>
      @foreach($budgets as $key => $value)
        <option value="{{$key}}" {{ ($key == $expenseBudget->budget__c) ? 'selected' : '' }}>{{$value}}</option>
      @endforeach
    </select>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">@lang("messages.Amount")<span class="red"> *</span></label>
    <input type="number" min="0.00" step="0.01" class="form-control custom-select" required value="{{ old('amount', $expenseBudget->amount__c )}}" name="amount">
    @if ($errors->has('amount'))
      <br/>
      <label class="col-sm-2 control-label"></label>
      <span style="color: #dd4b39; margin-left: 15px">{{ $errors->first('amount') }}</span>
    @endif
  </div>

  @if($errors->has('message'))
  <div class="form-group">
    <label class="col-sm-2 control-label"></label>
    <div class="col-sm-5">
      <span style="color: #dd4b39;">{{ $errors->first('message') }}</span>
    </div>
  </div>
  @endif

</div>
<div class="box-footer button-footer">
  @if($apiConnect)
    <button type="submit" id="submit" class="btn btn-info">@lang("messages.Submit")</button>
  @else
    <button type="button" onclick="checkConnectSf()" class="btn btn-info">@lang("messages.Submit")</button>
  @endif
  <a href="{{ isset($linkRedirect) ? $linkRedirect : 'javascript:history.back()'}}" class="btn btn-default">@lang("messages.Cancel")</a>
</div>
