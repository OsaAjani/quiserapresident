#!/bin/sh
# filename: manager.sh

PROCESSORS=5;
x=0
SCRIPTDIR=$(dirname $(readlink -f $0))

while [ "$x" -lt "$PROCESSORS" ];
do
    PROCESS_COUNT=`pgrep -f analyseNewTweets | wc -l`
    if [ $PROCESS_COUNT -ge $PROCESSORS ]; then
        exit 0
    fi
    x=`expr $x + 1`
    `$SCRIPTDIR/console.php '\\controllers\\internals\\TwitterBot' 'analyseNewTweets' > /dev/null 2>&1` &
done
exit 0
