apk add --update wget php7 php7-dev build-base autoconf re2c libtool
mkdir -p /tmp

wget http://ftp.gnu.org/pub/gnu/libiconv/libiconv-1.16.tar.gz -O /tmp/libiconv.tar.gz
tar xf /tmp/libiconv.tar.gz -C /tmp
cd /tmp/libiconv-1.16/
sed -i 's/_GL_WARN_ON_USE (gets, "gets is a security hole - use fgets instead");/#if HAVE_RAW_DECL_GETS\n_GL_WARN_ON_USE (gets, "gets is a security hole - use fgets instead");\n#endif/g' srclib/stdio.in.h
./configure --prefix=/usr/local
make && make install

php_version=`php -r "phpinfo();" | grep "PHP Version" | head -1`
php_version=${php_version#*=> }
wget http://php.net/distributions/php-$php_version.tar.gz -O /tmp/php.tar.gz
tar xf /tmp/php.tar.gz -C /tmp
cd /tmp/php-$php_version/ext/iconv
phpize
./configure --with-iconv=/usr/local
make && make install

mkdir /tmp/iconv
cp /usr/local/lib/libiconv.so /tmp/iconv
cp /usr/lib/php7/modules/iconv.so /tmp/iconv