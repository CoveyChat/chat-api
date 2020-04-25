<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @component('components.head')
        <strong>Whoops!</strong> Something went wrong!
    @endcomponent

    <style>
        body {
            background-color: #5e77e3;
        }

        .row.body {
            padding-bottom:20px;
        }
        .title {
            font-size: 5em;
        }
        .phonetic {
            color:#fff !important;
        }
        ol.definition {
            color: #000;
        }
        ol.definition > li > span.text-muted {
            color:#333 !important;
        }

        @media screen and (max-width: 425px) {
            .links > a {
                padding:0 15px;
            }
        }

        @media screen and (min-width: 426px) {
            .links > a {
                padding: 0 25px;
            }
        }

        .links > a {
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }
        .links {
            margin-top:20px;
        }
        ol {
            list-style-type: none;
        }
        .row.footer {
            position:fixed;
            bottom:0px;
            left:0px;
            right:0px;
            background-color: #5e77e3;
            color:#fff;
        }
        .row.footer a {padding-left:15px;padding-right:15px;color:#fff;}
        .row.footer a:hover {color:#dedede;}
        .row.footer a:active {color:#eee;}

    </style>

</head>
<body>
    <div id="app" class="container-fluid">
        <div class="row">
            <div class="col-md-12 text-right links">
                @auth
                    <a href="{{ url('/profile') }}">My Account</a>
                @else
                    <a href="{{ route('login') }}">Login</a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}">Register</a>
                    @endif
                @endauth
                <a href="{{ url('/chat') }}">Create Chat</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 text-center">
                <img src='{{URL::asset('img/logo_color.png')}}' alt='BevyChat Logo' class='img-fluid mx-auto' />
            </div>
        </div>
        <div class="row body">
            @yield('content')
        </div>
        <div class="spacer">
        <div class="row footer">
            <div class="col-12">
                <footer class="text-center">
                    <a href="/privacy">Privacy Policy</a> |
                    <a href="/terms">Terms of Service</a>
                </footer>
            </div>
        </div>
    </div>

</body>
</html>
