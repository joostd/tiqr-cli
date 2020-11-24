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

log "[Starting new tiqr login]"

R=$(bin/login-start.php)
echo $R | jq
SID=$(echo $R | jq .sid | xargs)
URL=$(echo $R | jq .url | xargs)

# generate QR code for scanning
hint "$URL"
qrencode -t ANSI256 $URL

green "[now scan the QR code. Choose to enter a PIN, enter the PIN '1112'. should get an error message:]"
blue "Wrong PIN"
blue "You supplied an incorrect PIN. Please enter your PIN again."

green "[Choose Back(<). Choose 'Use PIN', enter the PIN '1111'. You should get a succes message:]"
blue "You have succesfully logged in!"

