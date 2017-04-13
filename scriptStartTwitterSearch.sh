#!/bin/sh
# filename: manager.sh

PROCESSORS=1;
x=0
SCRIPTDIR=$(dirname $(readlink -f $0))

while [ "$x" -lt "$PROCESSORS" ];
do
    PROCESS_COUNT=`pgrep -f searchForNewTweets | wc -l`
    if [ $PROCESS_COUNT -ge $PROCESSORS ]; then
        exit 0
    fi
    x=`expr $x + 1`
    `$SCRIPTDIR/console.php '\\controllers\\internals\\TwitterBot' 'searchForNewTweets' > /dev/null 2>&1` &
done
exit 0
