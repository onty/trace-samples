@ECHO OFF
cd..
java -Xms384m -Xmx1024m -XX:MaxNewSize=224m -XX:NewSize=224m -XX:+UseConcMarkSweepGC -XX:CMSInitiatingOccupancyFraction=60 -XX:+UseCMSInitiatingOccupancyOnly -XX:SurvivorRatio=8 -XX:MaxTenuringThreshold=6 -XX:+PrintGCTimeStamps -jar ffs.jar %*
cd bin