# $OpenBSD: dot.profile,v 1.5 2005/03/30 21:18:33 millert Exp $
#
# sh/ksh initialization

alias l='colorls -alFG'
alias ls='colorls -G'
alias ..='cd ..'

# cyrilic  ksh
set +o emacs-usemeta
export LC_CTYPE="ru_RU.UTF-8"

export PKG_PATH=./:ftp://mirror.internode.on.net/pub/OpenBSD/`uname -r`/packages/`machine -a`/

export PS1="\w # "

PATH=/sbin:/usr/sbin:/bin:/usr/bin:/usr/X11R6/bin:/usr/local/sbin:/usr/local/bin
export PATH
: ${HOME='/root'}
export HOME
umask 022

if [ -x /usr/bin/tset ]; then
    eval `/usr/bin/tset -sQ \?$TERM`
fi