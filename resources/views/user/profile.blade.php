@extends('/layout/app')

@section('CSS')
    <style type="text/css">
        .centered {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            position: absolute;
        }
    </style>
@endsection

@section('content')
<section class="content-header">
    <h1>
       @lang('messages.Profile')
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="row" style="height: 200px;">
                    <div class="centered">
                        <div class="col-xs-6 col-xs-offset-3" style="text-align: center; margin-bottom:10px">@lang('messages.Status'): {{ isset($api) ? $api->status : __('messages.Disconnected') }}</div>
                        <div class="col-xs-6 col-xs-offset-3">
                            <a href="{{ route('authSalesforce') }}" class="btn btn-block {{ isset($api) && $api->status == __('messages.Synced') ? 'btn-danger' : 'btn-success' }}">
                                @if (!isset($api) || $api->status == __('messages.Disconnected'))
                                    @lang('messages.Synced')
                                @else
                                    @lang('messages.Disconnected')
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
