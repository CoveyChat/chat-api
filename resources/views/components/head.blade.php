<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">

<link rel="icon" href="{{ URL::asset('/img/favicon.png') }}" type="image/x-icon"/>

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ config('app.name', 'Bevy Chat') }} - For the birds</title>

<script type="text/javascript">
	window.addEventListener("error", function (e) {
			alert("Error occurred: " + e.error.message);
			document.write(e.error.message);
			return false;
	});
</script>

<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>

<!-- Fonts -->
<link rel="dns-prefetch" href="//fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

<!-- Styles -->
<link href="{{ asset('css/app.css') }}" rel="stylesheet">

<script src="https://d3js.org/d3.v5.min.js"></script>