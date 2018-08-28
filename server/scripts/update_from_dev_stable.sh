#!/bin/bash

{
    umask 0077 \
    && tmpdir=`mktemp -dt "$(basename $0).XXXXXXXXXX"` \
    && test -d "${tmpdir}" \
    && cd "${tmpdir}"
} || {
    echo 'mktemp failed'
    exit 1
}

wget -O ispconfig3-dev.tar.gz "https://github.com/scoueille/ispconfig3/archive/stable-3.1.tar.gz"
tar xzf ispconfig3-dev.tar.gz

echo -n "Latest git version:  "
ls -1d ispconfig3-stable*
cd ispconfig3-stable*/install

php -q \
    -d disable_classes= \
    -d disable_functions= \
    -d open_basedir= \
    update.php

cd /tmp
rm -rf "${tmpdir}"

exit 0
