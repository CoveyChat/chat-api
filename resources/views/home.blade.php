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
        <div class="row">
            <div class="col-6 text-right">
                <h1 class='title'>Bevy</h1>
                <h2><span class="text-muted pr-2 phonetic">/ˈbevē/</span></h2>
                <blockquote class='mr-4'>
                    <p>
                        <h3><em>noun</em></h3>
                    </p>
                    <p>
                        <ol class='pr-3 definition'>
                            <li>
                                <span class="text-muted">1.</span> large group or collection
                            </li>
                            <li>
                                <span class="text-muted">2.</span> a group of animals and especially quail
                            </li>
                        </ul>
                    </p>
                </blockquote>
            </div>

            <div class="col-6">
                <h1 class='title'>Chat</h1>
                <h2><span class="text-muted pl-2 phonetic">/CHat/</span></h2>
                <blockquote class='ml-4'>
                    <p>
                        <h3><em>verb</em></h3>
                    </p>
                    <p>
                        <ol class='pl-3 definition'>
                            <li>
                                <span class="text-muted">1.</span> talk in a friendly and informal way.
                            </li>
                            <li>
                                <span class="text-muted">2.</span> to take part in an online discussion in a chat room
                            </li>
                        </ul>
                    </p>
                </blockquote>
            </div>
        </div>
    </div>
</body>
</html>
