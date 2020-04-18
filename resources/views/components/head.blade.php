<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">

<link rel="apple-touch-icon" sizes="180x180" href="{{ URL::asset('/img/icons/apple-touch-icon.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ URL::asset('/img/icons/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ URL::asset('/img/icons/favicon-16x16.png') }}">
<link rel="manifest" href="{{ URL::asset('/img/icons/site.webmanifest') }}">
<link rel="mask-icon" href="{{ URL::asset('/img/icons/safari-pinned-tab.svg" color="#5e77e3') }}">
<link rel="shortcut icon" href="{{ URL::asset('/img/icons/favicon.ico') }}">
<meta name="msapplication-TileColor" content="#99ef9f') }}">
<meta name="msapplication-config" content="{{ URL::asset('/img/icons/browserconfig.xml') }}">
<meta name="theme-color" content="#99ef9f">

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ config('app.name', 'Bevy Chat') }} - Meetings made easy</title>

<script type="text/javascript">
    window.addEventListener("error", function (e) {
            alert("Error occurred: " + e.error.message);
            document.write(e.error.message);
            return false;
    });

    try {
        //Backfills for Mozilla / Safari
        navigator.mediaDevices.getUserMedia = navigator.mediaDevices.getUserMedia ||
        navigator.webkitGetUserMedia ||
        navigator.mozGetUserMedia;
    } catch (e) {
        //document.write("Your device does not support video. If you see Jake, tell him this:<br /><pre>" + JSON.stringify(e) + "</pre><br />");
        //alert("Your device does not support video. If you see Jake, tell him this:\n\n" + JSON.stringify(e));
    }
</script>

<!-- Scripts -->
<script src="{{ asset('js/app.js') }}" defer></script>

<!-- Fonts -->
<link rel="dns-prefetch" href="//fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

<!-- Styles -->
<link href="{{ asset('css/app.css') }}" rel="stylesheet">

<script src="https://d3js.org/d3.v5.min.js"></script>