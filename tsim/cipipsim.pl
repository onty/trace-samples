#!/usr/bin/perl -w
################################################################################
# Author: Mikael Persson
################################################################################
# Created: 2011-05-03
################################################################################
# Description:
# Wrapper for FFS to start a simluated call over CIP/IP
#
################################################################################
# Version history:
# 1.3     2012-09-18 CDR files can be created
# 1.2     2012-09-11 Paths can be changed with parameters
# 1.1     2012-01-26 Add microseconds to filename
# 1.0     2011-05-08 First version
################################################################################

use strict;
use File::Path qw(mkpath rmtree);
use Getopt::Long qw(:config pass_through);
use Time::HiRes qw( gettimeofday );

my $version="1.3";
my $modifiedDate="2012-09-18";
my $trafficcase="";
my $ffspath="/opt/ffs";
my $trafficcasepath="/var/opt/ffs/usecases";
my $logpath="/var/opt/ffs/logs";
my $help=0;
my ($seconds, $microseconds) = gettimeofday;
my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime($seconds);
my $timestamp=sprintf("%d%02d%02d_%02d%02d%02d_%06d",$year+1900,$mon+1,$mday,$hour,$min,$sec,$microseconds);
my $compacttimestamp=sprintf("%d%02d%02d%02d%02d%02d%06d",$year+1900,$mon+1,$mday,$hour,$min,$sec,$microseconds);

my $filename="cipipsim_${timestamp}";
my $cdrpath="";
my $cdrprefix="SDPCCR";
my $cdrsuffix="CDR";


GetOptions("trafficcase=s" => \$trafficcase,
           "ucpath=s" => \$trafficcasepath,
           "logpath=s" => \$logpath,
           "ffspath=s" => \$ffspath,
           "cdrpath=s" => \$cdrpath,
           "help|h" => \$help) || printhelp(); 

sub printhelp
{
	my $extrahelp=shift;
  my $scriptname=$0;
  $scriptname =~ s(^.*/)();
  my $scriptnamelength=length($scriptname);
  my $whitespace=sprintf("% ${scriptnamelength}s",'');
  print "$extrahelp\n" if (length($extrahelp));
  print "********************************\n";
  print "** Simulate call over CIP/IP v$version $modifiedDate\n";
  print "********************************\n";
  print "Usage:\n";
  print "$scriptname --trafficcase XXX\n";
  #print "$whitespace \n";
  print "    --trafficcase   Specify the trafficcase, SMS VOICE1 DATA\n";
  print "    --ucpath        Path to usecase configuration Default: /var/opt/ffs/usecases\n";
  print "    --logpath       Path to logfiles Default: /var/opt/ffs/logs\n";
  print "    --ffspath       Path ffs installation Default: /opt/ffs\n";
  print "    --cdrpath       Path to CDRs Default: do not write CDRs\n";
  print "    --help          Print this help.\n";
  exit 1;
} 
print "-------";
print $trafficcase;
print "-------";
#set config file
if (-f $trafficcase) {
  $trafficcase="$trafficcase"
} elsif (-f "$trafficcasepath/$trafficcase") {
  $trafficcase="$trafficcasepath/$trafficcase"
} elsif ($trafficcase eq "SMS") {
  $trafficcase="$trafficcasepath/sms/cipip_sms.xml"
} elsif($trafficcase eq "VOICE1") {
  $trafficcase="$trafficcasepath/voice_short/cipip_voice_short.xml"
} elsif($trafficcase eq "DATA") {
  $trafficcase="$trafficcasepath/data/cipip_data.xml"
} else {
  printhelp("Invalid or missing traffic case. $trafficcase");
}

$SIG{CHLD}="IGNORE";


my $pid = fork();
if (not defined $pid) {
  print "Fork failed, resources not avilable.\n";
  exit 1;
} elsif ($pid == 0) {
  my $tmplogpath="$logpath/tmp$compacttimestamp";
  mkpath($tmplogpath);

  open(STDIN,"</dev/null");
  open(STDOUT,">$logpath/${filename}_$$.log");
  chdir("$ffspath/");
  my $overrides="-override";
  foreach my $arg (@ARGV) {
    if ($arg =~ s/^--(.*)/$1/) {
      $overrides.=" $1=";
    } else {
      $overrides.="$arg";
    }
  }
  open(FFS,"/usr/bin/java -Dfile.encoding=ISO-8859-1 -Djava.library.path=lib/ -Djava.endorsed.dirs=lib/endorsed -Xms384m -Xmx1024m -XX:MaxNewSize=224m -XX:NewSize=224m -XX:+UseConcMarkSweepGC -XX:CMSInitiatingOccupancyFraction=60 -XX:+UseCMSInitiatingOccupancyOnly -XX:SurvivorRatio=8 -XX:MaxTenuringThreshold=6 -XX:+PrintGCTimeStamps -Dffs.threads=8 -Dffs.logpath=$tmplogpath -Dffs.logprefix=${filename}_$$ -jar ffs.jar -config=$trafficcase $overrides 2>&1 |");
    print "$overrides\n";
;
  $| = 1;
  while (<FFS>) {
    print $_;
  }

  if (-d $cdrpath) {
    my $cdrfile="${cdrprefix}_${timestamp}_${$}.${cdrsuffix}";
    my $cdrtmpfile="$cdrfile.tmp";
    open(DATA,"< $tmplogpath/${filename}_${$}LATEST_data.log");
    undef local $/;
    my $data=<DATA>;
    close(DATA);
    while ($data =~ /AVP.*?code=\"1064\".*?value=\"(.*?)\"/g) {
      my $cdr=$1;
      
      $cdr =~ s/&quot;/chr(34)/eg;
      $cdr =~ s/&apos;/chr(39)/eg;
      $cdr =~ s/&lt;/chr(60)/eg;
      $cdr =~ s/&gt;/chr(62)/eg;
      $cdr =~ s/&nbsp;/chr(160)/eg;
      $cdr =~ s/&iexcl;/chr(161)/eg;
      $cdr =~ s/&cent;/chr(162)/eg;
      $cdr =~ s/&pound;/chr(163)/eg;
      $cdr =~ s/&curren;/chr(164)/eg;
      $cdr =~ s/&yen;/chr(165)/eg;
      $cdr =~ s/&brvbar;/chr(166)/eg;
      $cdr =~ s/&sect;/chr(167)/eg;
      $cdr =~ s/&uml;/chr(168)/eg;
      $cdr =~ s/&copy;/chr(169)/eg;
      $cdr =~ s/&ordf;/chr(170)/eg;
      $cdr =~ s/&laquo;/chr(171)/eg;
      $cdr =~ s/&not;/chr(172)/eg;
      $cdr =~ s/&shy;/chr(173)/eg;
      $cdr =~ s/&reg;/chr(174)/eg;
      $cdr =~ s/&macr;/chr(175)/eg;
      $cdr =~ s/&deg;/chr(176)/eg;
      $cdr =~ s/&plusmn;/chr(177)/eg;
      $cdr =~ s/&sup2;/chr(178)/eg;
      $cdr =~ s/&sup3;/chr(179)/eg;
      $cdr =~ s/&acute;/chr(180)/eg;
      $cdr =~ s/&micro;/chr(181)/eg;
      $cdr =~ s/&para;/chr(182)/eg;
      $cdr =~ s/&middot;/chr(183)/eg;
      $cdr =~ s/&cedil;/chr(184)/eg;
      $cdr =~ s/&sup1;/chr(185)/eg;
      $cdr =~ s/&ordm;/chr(186)/eg;
      $cdr =~ s/&raquo;/chr(187)/eg;
      $cdr =~ s/&frac14;/chr(188)/eg;
      $cdr =~ s/&frac12;/chr(189)/eg;
      $cdr =~ s/&frac34;/chr(190)/eg;
      $cdr =~ s/&iquest;/chr(191)/eg;
      $cdr =~ s/&times;/chr(215)/eg;
      $cdr =~ s/&divide;/chr(247)/eg;
      $cdr =~ s/&Agrave;/chr(192)/eg;
      $cdr =~ s/&Aacute;/chr(193)/eg;
      $cdr =~ s/&Acirc;/chr(194)/eg;
      $cdr =~ s/&Atilde;/chr(195)/eg;
      $cdr =~ s/&Auml;/chr(196)/eg;
      $cdr =~ s/&Aring;/chr(197)/eg;
      $cdr =~ s/&AElig;/chr(198)/eg;
      $cdr =~ s/&Ccedil;/chr(199)/eg;
      $cdr =~ s/&Egrave;/chr(200)/eg;
      $cdr =~ s/&Eacute;/chr(201)/eg;
      $cdr =~ s/&Ecirc;/chr(202)/eg;
      $cdr =~ s/&Euml;/chr(203)/eg;
      $cdr =~ s/&Igrave;/chr(204)/eg;
      $cdr =~ s/&Iacute;/chr(205)/eg;
      $cdr =~ s/&Icirc;/chr(206)/eg;
      $cdr =~ s/&Iuml;/chr(207)/eg;
      $cdr =~ s/&ETH;/chr(208)/eg;
      $cdr =~ s/&Ntilde;/chr(209)/eg;
      $cdr =~ s/&Ograve;/chr(210)/eg;
      $cdr =~ s/&Oacute;/chr(211)/eg;
      $cdr =~ s/&Ocirc;/chr(212)/eg;
      $cdr =~ s/&Otilde;/chr(213)/eg;
      $cdr =~ s/&Ouml;/chr(214)/eg;
      $cdr =~ s/&Oslash;/chr(216)/eg;
      $cdr =~ s/&Ugrave;/chr(217)/eg;
      $cdr =~ s/&Uacute;/chr(218)/eg;
      $cdr =~ s/&Ucirc;/chr(219)/eg;
      $cdr =~ s/&Uuml;/chr(220)/eg;
      $cdr =~ s/&Yacute;/chr(221)/eg;
      $cdr =~ s/&THORN;/chr(222)/eg;
      $cdr =~ s/&szlig;/chr(223)/eg;
      $cdr =~ s/&agrave;/chr(224)/eg;
      $cdr =~ s/&aacute;/chr(225)/eg;
      $cdr =~ s/&acirc;/chr(226)/eg;
      $cdr =~ s/&atilde;/chr(227)/eg;
      $cdr =~ s/&auml;/chr(228)/eg;
      $cdr =~ s/&aring;/chr(229)/eg;
      $cdr =~ s/&aelig;/chr(230)/eg;
      $cdr =~ s/&ccedil;/chr(231)/eg;
      $cdr =~ s/&egrave;/chr(232)/eg;
      $cdr =~ s/&eacute;/chr(233)/eg;
      $cdr =~ s/&ecirc;/chr(234)/eg;
      $cdr =~ s/&euml;/chr(235)/eg;
      $cdr =~ s/&igrave;/chr(236)/eg;
      $cdr =~ s/&iacute;/chr(237)/eg;
      $cdr =~ s/&icirc;/chr(238)/eg;
      $cdr =~ s/&iuml;/chr(239)/eg;
      $cdr =~ s/&eth;/chr(240)/eg;
      $cdr =~ s/&ntilde;/chr(241)/eg;
      $cdr =~ s/&ograve;/chr(242)/eg;
      $cdr =~ s/&oacute;/chr(243)/eg;
      $cdr =~ s/&ocirc;/chr(244)/eg;
      $cdr =~ s/&otilde;/chr(245)/eg;
      $cdr =~ s/&ouml;/chr(246)/eg;
      $cdr =~ s/&oslash;/chr(248)/eg;
      $cdr =~ s/&ugrave;/chr(249)/eg;
      $cdr =~ s/&uacute;/chr(250)/eg;
      $cdr =~ s/&ucirc;/chr(251)/eg;
      $cdr =~ s/&uuml;/chr(252)/eg;
      $cdr =~ s/&yacute;/chr(253)/eg;
      $cdr =~ s/&thorn;/chr(254)/eg;
      $cdr =~ s/&yuml;/chr(255)/eg;
      $cdr =~ s/&#(\d+);/chr($1)/eg;
      $cdr =~ s/&amp;/chr(38)/eg;
      
      open(CDR,">>$cdrpath/$cdrtmpfile") or print "Failed to open temporary CDR file \"$cdrpath/$cdrtmpfile\": $!\n";
      print CDR $cdr;
      close(CDR);
    }
    if (-f "$cdrpath/$cdrtmpfile") {
      rename "$cdrpath/$cdrtmpfile","$cdrpath/$cdrfile";
      print "CDR $cdrpath/$cdrfile created.\n";
    }
  } else {
    print "No CDR file was created since cdr path \"$cdrpath\" does not exist.\n";
  }
  rename "$tmplogpath/${filename}_${$}LATEST_data.log","$logpath/${filename}_${$}LATEST_data.log";
  rmtree($tmplogpath);
  print "Session completed\n"; #IMPORTANT js needs this to stop scanning a session
  close(FFS);
  exit(0);
} else {
  #IMPORTANT js needs this to continue to scan the right file
  print "Session: ${filename}_$pid.log\n";
}

