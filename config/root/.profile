# $OpenBSD: dot.profile,v 1.5 2005/03/30 21:18:33 millert Exp $
#
# sh/ksh initialization

alias l='ls -alF'
alias ..='cd ..'

export PKG_PATH=./:http://ftp.jaist.ac.jp/pub/OpenBSD/`uname -r`/packages/`machine -a`/

export PS1="\w # "

PATH=/sbin:/usr/sbin:/bin:/usr/bin:/usr/X11R6/bin:/usr/local/sbin:/usr/local/bin
export PATH
: ${HOME='/root'}
export HOME
umask 022

if [ -x /usr/bin/tset ]; then
    eval `/usr/bin/tset -sQ \?$TERM`
fi