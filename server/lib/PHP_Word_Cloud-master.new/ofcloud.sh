#!/bin/sh

SCRIPTLOCATION="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPTLOCATION"

OFOC="com.omnigroup.OmniFocus"
if [ ! -d "$HOME/Library/Caches/$OFOC" ]; then OFOC=$OFOC.MacAppStore; fi
OFQUERY="sqlite3 $HOME/Library/Caches/$OFOC/OmniFocusDatabase2"

TODAY=$(date -v0H -v0M -v0S +%s)
NOW=$(date +%s)
DAY=86400
NEXTWEEK=$(($NOW + (7 * $DAY)))
YESTERDAY=$(($NOW - $DAY))

ZONERESET=$(date +%z | awk '{if (substr($1,1,1)!="+") {printf "+"} else {printf "-"} print substr($1,2,4)}') 
YEARZERO=$(date -j -f "%Y-%m-%d %H:%M:%S %z" "2001-01-01 0:0:0 $ZONERESET" "+%s")
STARTS="($YEARZERO + t.effectiveDateToStart)"
DUE="($YEARZERO + t.effectiveDateDue)"
DAY=86400

MATCHES="(($STARTS < ($NOW + (7 * $DAY))) and ($STARTS >= $TODAY)) or ((t.dateCompleted is null and  $DUE < ($NOW + (7 * $DAY))) and ($DUE >= $TODAY)) or (t.dateCompleted is null and $DUE < $TODAY)"

$OFQUERY "
SELECT p.name, t.name
FROM (((task tt left join projectinfo pi on tt.containingprojectinfo=pi.pk) t
left join task p on t.task=p.persistentIdentifier)
left join context c on t.context = c.persistentIdentifier)
left join folder f on t.folder=f.persistentIdentifier
WHERE $MATCHES
ORDER BY t.effectiveDateDue, f.name, p.name, c.name
" | tr '|' ' ' > tmp.txt

rm *.png

IMG=$(/usr/bin/php -f ./tagcloud.php tmp.txt 1280 800)
./setWallpaper "$IMG"

rm tmp.txt

rm tmp.txt