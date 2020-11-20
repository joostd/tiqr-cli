#!/bin/bash

#
# start registration
#


[ -z "$PROXY" ] && PROXY=http://localhost:8080

echo "$(tput setaf 5)[Starting new tiqr registration...]$(tput setaf 0)"
R=$(bin/register-start.php -p $PROXY)
echo $R | jq
SID=$(echo $R | jq .sid | xargs)
USERID=$(echo $R | jq .uid | xargs)
URL=$(echo $R | jq .url | xargs)

echo "$(tput setaf 5)feed your tiqr client with the following URI:$(tput setaf 0)"
# generate QR code for scanning
echo "$(tput setaf 9)$URL$(tput setaf 0)"
qrencode -t ANSI256 $URL

echo "$(tput setaf 5)[waiting for tiqr client...]$(tput setaf 0)"

# finish registration
R=$(bin/register-finish.php -s $SID)
echo $R | jq

#
# start login
#

echo "$(tput setaf 5)[Starting new tiqr login...]$(tput setaf 0)"

R=$(bin/login-start.php -u $USERID)
echo $R | jq
SID=$(echo $R | jq .sid | xargs)
URL=$(echo $R | jq .url | xargs)

# generate QR code for scanning
echo "$(tput setaf 9)$URL$(tput setaf 0)"
qrencode -t ANSI256 $URL

# finish login
echo "$(tput setaf 5)[waiting for tiqr client...]$(tput setaf 0)"
bin/login-finish.php -s $SID | jq

#
# start re-authentication using push
#

echo "$(tput setaf 5)[Starting re-authentication...]$(tput setaf 0)"

R=$(bin/login-start.php -u $USERID -m)
echo $R | jq
SID=$(echo $R | jq .sid | xargs)
URL=$(echo $R | jq .url | xargs)

# generate QR code for scanning
echo "$(tput setaf 9)$URL$(tput setaf 0)"

# finish login
echo "$(tput setaf 5)[waiting for tiqr client...]$(tput setaf 0)"
bin/login-finish.php -s $SID | jq
