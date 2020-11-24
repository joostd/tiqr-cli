#!/bin/bash

#
# test expire message
#

source $(dirname $0)/common.sh

[ -z "$PROXY" ] && PROXY=http://localhost:8080

cls
blue "[using tiqr authentication server $PROXY]"

#
# start login
#

log "[Starting new tiqr login for...]"

R=$(bin/login-start.php)
echo $R | jq
SID=$(echo $R | jq .sid | xargs)
URL=$(echo $R | jq .url | xargs)

log "[waiting for challenge to expire...]"
sleep 180

# generate QR code for scanning
hint "$URL"
qrencode -t ANSI256 $URL

green "[now scan the QR code. You should get an error message:]"
blue "Invalid Challenge"
blue "The scanned QR code is invalid or has expired."
blue "please try again."
