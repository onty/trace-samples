#!/usr/bin/perl

use strict;
use Getopt::Long;

my $version=0;
my $help=0;
my $packagename="trafficsim";

sub printhelp
{
	my $extrahelp=shift;
  my $progname=$0;
  $progname =~ s/.*\/(.*)$/$1/;
  print "$extrahelp\n\n" if (length($extrahelp));
  print "Usage: $progname \n";
  print "    -v n.n      New version.\n";
  print "    -h          Print this help.\n";
  exit 1;
}
GetOptions("version=s" => \$version,
           "help|h" => \$help) || printhelp();
printhelp("Please supply the new version.") unless $version;
printhelp() if $help;

my $tarpackagename="${packagename}-$version.tar";
my $fullpackagename="${packagename}-$version.tar.gz";

die "Package already exists.\n" if (-f $fullpackagename);

my $uname=`uname -a`;

if ($uname =~ m/SunOS/) {
  print "SunOS detected\nCreating file ${fullpackagename}\n";
  `ls -1 ${packagename}*.tar* config.inc.php config/configuration 2>/dev/null > exclude-file`;
  `echo "exclude-file" >> exclude-file`;
  `tar cfX ${tarpackagename} exclude-file *`;
  `gzip ${tarpackagename}`;
  `/usr/bin/rm exclude-file`;
} else {
  print "Creating file ${fullpackagename}\n";
  `tar cfz ${fullpackagename} * --exclude "config/configuration" --exclude config.inc.php --exclude "${packagename}*.tar.gz" --exclude "${packagename}*.tar"`;
}


