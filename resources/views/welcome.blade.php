<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Bevy Chat - A chat for the birds</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }


            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

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

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @auth
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ route('login') }}">Login</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

            <div class="content">
                <div class="m-b-md">
                    <img src='{{URL::asset('img/logo_color.png')}}' alt='BevyChat Logo' />
                    <h1 class='title'>Bev·y</h1>
                    <p>
                        <span class="text-muted">/ˈbevē/</span>
                    </p>
                    <blockquote>
                        <p>
                            <em>noun</em>
                        </p>
                        <p>
                            <ul>
                                <li>
                                    a large group of people or things of a particular kind.
                                    "a bevy of big-name cameos will keep the adults entertained"
                                </li>
                                <li>
                                    a group of birds, especially quail, particularly when closely gathered on the ground.
                                </li>
                            </ul>
                        </p>
                    </blockquote>
                </div>
            </div>
        </div>
    </body>
</html>
