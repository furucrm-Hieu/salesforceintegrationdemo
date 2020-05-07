<div class="box-body">
  <div class="form-group">
    <label class="col-sm-2 control-label">@lang("messages.Proposal_Name") <span class="red"> *</span></label>
    <div class="col-sm-5">
      <input type="text" class="form-control" name="name" value="{{old('name', isset($proposal->name) ? $proposal->name : '')}}" required>
      @if ($errors->has('name'))
        <span class="red">{{ $errors->first('name') }}</span>
      @endif
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">@lang("messages.Year")<span class="red"> *</span></label>
    <div class="col-sm-5">
      <input type="number" min="1000" max="3000" class="form-control" value="{{old('year', isset($proposal->year__c) ? $proposal->year__c : '')}}" name="year" required>
      @if ($errors->has('year'))
        <span class="red">{{ $errors->first('year') }}</span>
      @endif
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">@lang("messages.ProposalAt")<span class="red"> *</span></label>
    <div class="col-sm-5">
      <div class="input-group">
        <div class="input-group-addon">
          <i class="fa fa-calendar"></i>
        </div>
        <input type="text" name="proposed_at" value="{{ old('proposed_at', isset($proposal->proposed_at__c) ? HelperDateTime::convertDateTimeUtcToJp($proposal->proposed_at__c) : '' )}}" class="form-control pull-right" id="proposed_at">
      </div>
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">@lang("messages.ApprovedAt") <span class="red"> *</span></label>
    <div class="col-sm-5">
      <div class="input-group">
        <div class="input-group-addon">
          <i class="fa fa-calendar"></i>
        </div>
        <input type="text" name="approved_at" value="{{ old('approved_at', isset($proposal->approved_at__c) ? HelperDateTime::convertDateTimeUtcToJp($proposal->approved_at__c) : '' )}}" class="form-control pull-right" id="approved_at">
      </div>
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">@lang("messages.Detail")</label>
    <div class="col-sm-5">
      <textarea class="form-control" rows="3" name="detail">{{ old('detail', isset($proposal->details__c) ? $proposal->details__c : '' )}}</textarea>
      @if ($errors->has('detail'))
        <span class="red">{{ $errors->first('detail') }}</span>
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
  @if($apiConnect)
    <button type="submit" id="submit" class="btn btn-info">@lang("messages.Submit")</button>
  @else
    <button type="button" onclick="checkConnectSf()" class="btn btn-info">@lang("messages.Submit")</button>
  @endif
  <a href="{{ isset($proposal) ? url('proposal/'.$proposal->id) : url('proposal')}}" class="btn btn-default">@lang("messages.Cancel")</a>
</div>