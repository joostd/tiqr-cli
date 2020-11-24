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

USERID=nonexistinguser

log "[Starting new tiqr login for user '$USERID'...]"

R=$(bin/login-start.php -u $USERID)
echo $R | jq
SID=$(echo $R | jq .sid | xargs)
URL=$(echo $R | jq .url | xargs)

# generate QR code for scanning
hint "$URL"
qrencode -t ANSI256 $URL

green "[now scan the QR code. You should get an error message:]"
blue "Invalid account"
blue "You tried to log in with an invalid or unknown account."
blue "Please (re-)activate your account first."
