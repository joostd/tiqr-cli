# tiqr-cli - tiqr test scripts

These are command line scripts for testing different tiqr scenario's on the command line.

To run, you need [php-cli](https://www.php.net) (tested with php7) and [composer](https://getcomposer.org).

To install using composer:

    composer install

It also requires a tool to generate QR codes. I like to use [qrencode](https://github.com/fukuchi/libqrencode), available on MacOS using [brew](https://brew.sh)

    brew install qrencode

Test from the command line using

    qrencode -t ANSI256 somestring

Alternatively, you can open a web browser to render QR codes, for instance using [Google Charts](https://developers.google.com/chart):

    open 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=$url'

## tiqr authentication server

Start the tiqr authentication server:

    php -S 0:8080 -t www

### using a reverse proxy

To use tiqr on an Android or iOS device, tiqr needs to be able to communicate with an authentication server over a secure connection.
The `www` directory contains a simple implementation that you can run from e.g. your laptop, but that service isn't accessible from your mobile device.
You need to run a reverse proxy to connect to your device, such as [ngrok](https://ngrok.com/),
but you can also run your own using [nginx](https://www.nginx.com/)
or [Caddy](https://caddyserver.com/).
See [here](https://github.com/joostd/https-reverse-proxy-over-ssh)
and [here](https://github.com/joostd/ssh-reverse-proxy).

You need to set the https endpoint in the `PROXY` environment variable. For instance, using `ngrok`:

    ngrok http 8080

This will show the newly created endpoint, for instance

    export PROXY=https://abcdef012345.ngrok.io

### Push notifications

To use push notifications, you need an API key (Google Cloud Messaging) of certificate (Apple Push Notification).
You can specify these in the file `local_options.php`. See `local_options.php.example` for an example.

## Use from the command line

The php scripts in the `bin` directory are intended to be run from the command line.
Use these scripts with `-h` as an argument to list any options you can use.

See below for some examples.

### register a new user

```
$ bin/register-start.php -h
Usage: bin/register-start.php [-hv] [-u userid] [-n display name] [-p proxy]
	h : help
	n : specify name (default is dummy)
	p : use a proxy (default is none, assume http://localhost:8080)
	u : specify userid to register (default is timestamp in base-36)
	v : verbose
```

```
$ bin/register-start.php -p $PROXY -u jdoe | jq
{
  "url": "tiqrenroll://https://7ecfbf8337a0.ngrok.io/tiqr?key=3531a86b9d11186f6c9d42b4ec2531380ebebd7a8993ec5b9c1afa5f53945ae3",
  "sid": "cafebabe",
  "uid": "xkcd"
}
```

    qrencode -t ANSI256 tiqrenroll://https://7ecfbf8337a0.ngrok.io/tiqr?key=e5cb473d425a3c54b1ad2d26be42c592da7e18133a9909435046427d2336a4a2

```
$ bin/register-finish.php -s c3234848
```

The script will show a QR code on the terminal and output a random username.

### authenticate the new user

TODO

### re-authenticate the new user with a push notification

TODO


## Scripting

Above scripts can be combined to test different scenario's.
Examples are procided in the `scripts` directory.

For instance, the script `register-login-push.sh` will first register a new tiqr account, then ask to login with that account, and finally perform a re-authentication using a push message.

## Troubleshooting

Note that authentication endpoints are pinned during registration. If you change endpoints (typically when using dynamic ngrok endpoints) you need to delete any tiqr account on your device registered using the old endpoint. Your tiqr client will complain.

The tiqr authentication service stores accounts in the `/tmp` directory. This directory is periodically empties (e.g. after a reboot). Make sure that tiqr accounts stored on a device may no longer be known to the authentication server in that case.

