#!/bin/ksh
# $ComixWall: pnrg-wrapper.sh,v 1.2 2009/09/03 09:30:19 soner Exp $
/var/www/htdocs/comixwall/View/pmacct/pnrg/pnrg.pl --spool=/var/www/htdocs/comixwall/View/pmacct/pnrg/spool/
/var/www/htdocs/comixwall/View/pmacct/pnrg/pnrg-cgikeeper.pl --spool=/var/www/htdocs/comixwall/View/pmacct/pnrg/spool/
#/var/www/htdocs/comixwall/View/pmacct/pnrg/pnrg-indexmaker.pl --spool=/var/www/htdocs/comixwall/View/pmacct/pnrg/spool/
