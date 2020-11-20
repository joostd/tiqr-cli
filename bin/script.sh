#!/bin/sh

X=$(bin/tiqr.php -p https://proxy.aai.surfnet.nl -r)
echo registered $X
Y=$(bin/tiqr.php -p https://proxy.aai.surfnet.nl -m -u $X)

echo authenticated $Y
