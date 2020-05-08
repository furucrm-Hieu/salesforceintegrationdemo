@extends('layout.app')

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
                        <div class="col-xs-6 col-xs-offset-3" style="text-align: center; margin-bottom:10px">
                            <h4>@lang('messages.Status'): {{ isset($api) ? $api->expried ? __('messages.Disconnected') :  __('messages.Connected') : __('messages.Disconnected') }}</h4>
                        </div>
                        <div class="col-xs-6 col-xs-offset-3">
                            <a href="{{ route('authSalesforce') }}" class="btn btn-block {{ isset($api) ? $api->expried ? 'btn-success' : 'btn-danger' : 'btn-success' }}">
                                {{ !isset($api) ? __('messages.Connect')  : $api->expried ? __('messages.Connect') : __('messages.Disconnect')  }}
                            </a>
                             {{-- <a href="{{ route('refreshToken') }}" class="btn btn-block {{ isset($api) ? 'btn-success' : 'btn-danger' }}">
                                {{ !isset($api) ? __('messages.Connect')  : __('messages.Disconnect')}}
                            </a> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('JS')
    <script type="text/javascript">
        $(document).ready(function() {
            if({{ json_encode(isset($api->expried) && $api->expried ?? false) }}) {
                if(window.confirm("@lang('messages.Alert_Token_Expired')")) {
                    let refreshToken = document.createElement('A');
                    refreshToken.setAttribute('href', "{{ route('refreshToken') }}");
                    refreshToken.click();
                };
            }
        });
    </script>
@endsection
