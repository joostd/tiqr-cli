#!/bin/bash

#
# start registration
#

source $(dirname $0)/common.sh

[ -z "$PROXY" ] && PROXY=http://localhost:8080

cls
blue "[using tiqr authentication server $PROXY]"

log "[Starting new tiqr registration...]"
R=$(bin/register-start.php -p $PROXY)
echo $R | jq
SID=$(echo $R | jq .sid | xargs)
USERID=$(echo $R | jq .uid | xargs)
URL=$(echo $R | jq .url | xargs)

log "feed your tiqr client with the following URI:"
# generate QR code for scanning
hint "$URL"
qrencode -t ANSI256 $URL

log "[waiting for tiqr client...]"

# finish registration
R=$(bin/register-finish.php -s $SID)
echo $R | jq

#
# start login
#

log "[Starting new tiqr login...]"

R=$(bin/login-start.php -u $USERID)
echo $R | jq
SID=$(echo $R | jq .sid | xargs)
URL=$(echo $R | jq .url | xargs)

# generate QR code for scanning
hint "$URL"
qrencode -t ANSI256 $URL

# finish login
log "[waiting for tiqr client...]"
bin/login-finish.php -s $SID | jq

#
# start re-authentication using push
#

log "[Starting re-authentication...]"

R=$(bin/login-start.php -u $USERID -m)
echo $R | jq
SID=$(echo $R | jq .sid | xargs)
URL=$(echo $R | jq .url | xargs)

# generate QR code for scanning
hint "$URL"

# finish login
log "[waiting for tiqr client...]"
bin/login-finish.php -s $SID | jq
