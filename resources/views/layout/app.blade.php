<!DOCTYPE html>
<html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="shortcut icon" type="image/x-icon" href="{{asset('image/favicon.ico')}}">
  <title>Citron</title>

  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{asset('template/bower_components/bootstrap/dist/css/bootstrap.min.css')}}">
  <link rel="stylesheet" href="{{asset('template/bower_components/font-awesome/css/font-awesome.min.css')}}">
  <link rel="stylesheet" href="{{asset('template/bower_components/Ionicons/css/ionicons.min.css')}}">
  <link rel="stylesheet" href="{{asset('template/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css')}}">
  <link rel="stylesheet" href="{{asset('template/dist/css/AdminLTE.min.css')}}">
  <link rel="stylesheet" href="{{asset('template/dist/css/skins/_all-skins.min.css')}}">
  <link rel="stylesheet" href="{{asset('css/app.css')}}">

  @yield('CSS')

  <!-- Google Font -->
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div id="overlay" style="display:none;">
  <div class="spinner"></div>
  <br/>
  @lang('messages.Loading')...
</div>
<div class="wrapper">

  <header class="main-header">
    <!-- Logo -->
    <a href="javascript:void(0)" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b>C</b>itron</span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b>Citron</b></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">

          <li class="dropdown notifications-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              @php $locale = (!empty(session()->get('locale'))) ? session()->get('locale') : 'jp'; @endphp
              @switch($locale)
                @case('en')
                  <img width="20px" src="{{asset('image/flag-us.png')}}"> English
                @break
                @case('jp')
                  <img width="20px" src="{{asset('image/flag-jp.png')}}"> 日本語
                @break
                @default
                  <img width="20px" src="{{asset('image/flag-jp.png')}}"> 日本語
              @endswitch
            </a>
            <ul class="dropdown-menu" style="width: 20px; min-width: 87px !important;">
              <li class="footer"><a href="javascript:void(0)" onclick="changeLocalization(event, 'jp')"><img width="20px" src="{{asset('image/flag-jp.png')}}"> 日本語</a></li>
              <li class="footer"><a href="javascript:void(0)" onclick="changeLocalization(event, 'en')"><img width="20px" src="{{asset('image/flag-us.png')}}"> English</a></li>
            </ul>
          </li>

          <li class="dropdown tasks-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              {{Auth::user()->name}}
            </a>
            <ul class="dropdown-menu" style="width: 20px; min-width: 100px !important;">
              <li class="footer">
                <a href="{{ route('profile') }}">@lang('messages.Profile')</a>
              </li>
              <li class="footer">
                <a href="javascript:void(0)" onclick="event.preventDefault();document.getElementById('logout-form').submit();">@lang('messages.Sign_Out')</a>
              </li>
            </ul>
          </li>

        </ul>
      </div>

      <form id="logout-form" action="{{url('/post-logout')}}" method="POST" style="display: none;">
        @csrf
      </form>
    </nav>
    <section class="content-header" style="padding-bottom: 20px;">
      <a href="{{ route('proposal.index') }}" class="{{ Request::is('proposal') || Request::is('proposal/*') ? 'is-active': '' }}" style="margin-right: 10px; color:#fff;">@lang("messages.Proposal")</a>
      <a href="{{ route('budget.index') }}" class="{{ Request::is('budget') || Request::is('budget/*') ? 'is-active': '' }}" style="margin-right: 10px; color:#fff;">@lang("messages.Budget")</a>
      <a href="{{ route('expense.index') }}" class="{{ Request::is('expense') || Request::is('expense/*') ? 'is-active': '' }}" style="margin-right: 10px; color:#fff;">Expense</a>
    </section>
  </header>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper" style="margin-left: 0">
    @yield('content')
  </div>
  <!-- /.content-wrapper -->


  <div class="control-sidebar-bg"></div>
</div>

<script src="{{asset('template/bower_components/jquery/dist/jquery.min.js')}}"></script>
<script src="{{asset('template/bower_components/bootstrap/dist/js/bootstrap.min.js')}}"></script>
<script src="{{asset('template/bower_components/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('template/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script src="{{asset('template/bower_components/jquery-slimscroll/jquery.slimscroll.min.js')}}"></script>
<script src="{{asset('template/bower_components/fastclick/lib/fastclick.js')}}"></script>
<script src="{{asset('template/dist/js/adminlte.js')}}"></script>
<script src="{{asset('template/dist/js/demo.js')}}"></script>
<script src="{{asset('js/site_js.js')}}"></script>
<script type="text/javascript">
  $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  var base_url = "{!! url('/') !!}";
  var curLocale = "{!! $locale !!}";
  var tranlateConfirm = "{{ __('messages.Confirm_delete') }}";
  var dataLanguage = (curLocale == 'en') ? en_datatable : jp_datatable;
  var systemError = "{{ __('messages.System_Error') }}";
  var tokenError = "{{ __('messages.Token_Error') }}";

  function changeLocalization(event, locale) {
    event.preventDefault();

    if(curLocale == locale) {
      return false;
    }

    $('#overlay').fadeIn();
    $.ajax({
      url: base_url + '/lang/' + locale,
      type: 'GET',
      success: function (res) {
        if (res.success == true) {
          location.reload();
        }
        else {
          $('#overlay').fadeOut();
          alert(tokenError);
        }
      },
      error: function (res) {
        $('#overlay').fadeOut();
        alert(systemError);
      }
    });

  }

  function confirmDeleteAjax(event, link, id) {
    event.preventDefault();

    var r = confirm(tranlateConfirm);
    if (r == true) {
      $('#overlay').fadeIn();
      $.ajax({
        url: base_url + '/' + link + '/' + id,
        type: 'POST',
        data: { _method: 'DELETE'},
        success: function (res) {
          if (res.success == true) {
            location.reload();
          }
          else {
            $('#overlay').fadeOut();
            alert(tokenError);
          }
        },
        error: function (res) {
          $('#overlay').fadeOut();
          alert(systemError);
        }
      });
    }
  }

</script>
@yield('JS')
</body>
</html>

