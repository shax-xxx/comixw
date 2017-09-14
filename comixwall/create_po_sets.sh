#!/bin/sh
# $ComixWall: create_po_sets.sh,v 1.3 2009/11/11 15:30:45 soner Exp $

# Copyright (c) 2004-2009 Soner Tari.  All rights reserved.
#
# Redistribution and use in source and binary forms, with or without
# modification, are permitted provided that the following conditions
# are met:
# 1. Redistributions of source code must retain the above copyright
#    notice, this list of conditions and the following disclaimer.
# 2. Redistributions in binary form must reproduce the above copyright
#    notice, this list of conditions and the following disclaimer in the
#    documentation and/or other materials provided with the distribution.
# 3. All advertising materials mentioning features or use of this
#    software must display the following acknowledgement: This
#    product includes software developed by Soner Tari
#    and its contributors.
# 4. Neither the name of Soner Tari nor the names of
#    its contributors may be used to endorse or promote products
#    derived from this software without specific prior written
#    permission.
#
# THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
# IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
# OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
# IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
# INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
# NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
# DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
# THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
# (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
# THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

#/** \file
# Generates all po file sets for a given locale using create_po.sh.
# Echoes the number of msgid's in each
# Merges the sets
#*/

if [ $# -lt 1 ]; then
	echo "Not enough arguments [1]: $#"
	exit 1
fi

LOCALE=$1

./create_po.sh . $LOCALE comixwall_CONTROL.po _CONTROL
./create_po.sh . $LOCALE comixwall_MENU.po _MENU
./create_po.sh . $LOCALE comixwall_NOTICE.po _NOTICE
./create_po.sh . $LOCALE comixwall_TITLE.po _TITLE
./create_po.sh . $LOCALE comixwall_STATS.po _STATS
./create_po.sh . $LOCALE comixwall_HELPBOX.po _HELPBOX
./create_po.sh . $LOCALE comixwall_HELPWINDOW.po _HELPWINDOW
./create_po.sh . $LOCALE comixwall_TITLE2.po _TITLE2
./create_po.sh . $LOCALE comixwall_HELPBOX2.po _HELPBOX2
./create_po.sh . $LOCALE comixwall__.po _

echo -n 'CONTROL: '
grep msgid View/locale/$LOCALE/LC_MESSAGES/comixwall_CONTROL.po | wc -l

echo -n 'MENU: '
grep msgid View/locale/$LOCALE/LC_MESSAGES/comixwall_MENU.po | wc -l

echo -n 'NOTICE: '
grep msgid View/locale/$LOCALE/LC_MESSAGES/comixwall_NOTICE.po | wc -l

echo -n 'TITLE: '
grep msgid View/locale/$LOCALE/LC_MESSAGES/comixwall_TITLE.po | wc -l

echo -n 'STATS: '
grep msgid View/locale/$LOCALE/LC_MESSAGES/comixwall_STATS.po | wc -l

echo -n 'HELPBOX: '
grep msgid View/locale/$LOCALE/LC_MESSAGES/comixwall_HELPBOX.po | wc -l

echo -n 'HELPWINDOW: '
grep msgid View/locale/$LOCALE/LC_MESSAGES/comixwall_HELPWINDOW.po | wc -l

echo -n 'TITLE2: '
grep msgid View/locale/$LOCALE/LC_MESSAGES/comixwall_TITLE2.po | wc -l

echo -n 'HELPBOX2: '
grep msgid View/locale/$LOCALE/LC_MESSAGES/comixwall_HELPBOX2.po | wc -l

echo -n '_: '
grep msgid View/locale/$LOCALE/LC_MESSAGES/comixwall__.po | wc -l

msgcat -s --use-first -o View/locale/$LOCALE/LC_MESSAGES/comixwall.po View/locale/$LOCALE/LC_MESSAGES/comixwall_*.po
