<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @component('components.head')
        <strong>Whoops!</strong> Something went wrong!
    @endcomponent

    <style>
        .title {
            font-size: 84px;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
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
    </style>

</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 text-right links">
                @auth
                    <a href="{{ url('/home') }}">Home</a>
                @else
                    <a href="{{ route('login') }}">Login</a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}">Register</a>
                    @endif
                @endauth
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 text-center">
                <img src='{{URL::asset('img/logo_color.png')}}' alt='BevyChat Logo' class='img-fluid mx-auto' />
                <h1 class='title'>Bev·y</h1>
                <h2><span class="text-muted">/ˈbevē/</span></h2>
                <blockquote>
                    <p>
                        <h3><em>noun</em></h3>
                    </p>
                    <p>
                        <ol>
                            <li>
                                <span class="text-muted">1.</span> large group or collection
                            </li>
                            <li>
                                <span class="text-muted">2.</span> a group of animals and especially quail
                            </li>
                            <li>
                                <span class="text-muted">3.</span> a pretty nice video chat service
                            </li>
                        </ul>
                    </p>
                </blockquote>
            </div>
        </div>
    </div>
</body>
</html>
