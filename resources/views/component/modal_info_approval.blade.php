<div class="modal fade" id="modal-default">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang('messages.Approval_Process')</h4>
      </div>
      <div class="modal-body">
        <p id="text1"></p>
        <p id="text2"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">@lang('messages.Close')</button>
        <button type="button" class="btn btn-primary" onclick="postSubmitApproval(event)">@lang('messages.Submit')</button>
      </div>
    </div>
  </div>
</div>
