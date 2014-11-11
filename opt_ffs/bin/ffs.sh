#!/bin/sh

( cd "`dirname $0`/.." ; java -Djava.library.path=lib/ -Djava.endorsed.dirs=lib/endorsed -Xms384m -Xmx1024m -XX:MaxNewSize=224m -XX:NewSize=224m -XX:+UseConcMarkSweepGC -XX:CMSInitiatingOccupancyFraction=60 -XX:+UseCMSInitiatingOccupancyOnly -XX:SurvivorRatio=8 -XX:MaxTenuringThreshold=6 -XX:+PrintGCTimeStamps -Dffs.threads=8 -jar ffs.jar $@ )