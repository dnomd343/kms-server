apk add --no-cache git make build-base
git clone --branch master --single-branch https://github.com/Wind4/vlmcsd.git /tmp/vlmcsd-build
cd /tmp/vlmcsd-build
make
mkdir /tmp/vlmcsd
cp /tmp/vlmcsd-build/bin/vlmcsd /tmp/vlmcsd/
cp /tmp/vlmcsd-build/etc/vlmcsd.kmd /tmp/vlmcsd/
rm -rf /tmp/vlmcsd-build
