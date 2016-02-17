#!/bin/bash

VERSION=$1

RESET=$2
POSTFIX=
EXTRA_FLAGS=

if [ -z "$VERSION" ]; then
    echo "Usage: php-build.sh {VERSION}"
    echo ""
    echo "e.g. php-build.sh 5.2.11"
    exit;
fi

if [ -z "$RESET" ]; then
    RESET=0
fi


SRC_DIR=/home/vagrant/src
PHP_DIR=${SRC_DIR}/php-${VERSION}

if [ ! -d "$SRC_DIR" ]; then 
    mkdir ${SRC_DIR}
fi
cd $SRC_DIR

# If we don't have the src file downloaded, then we're going to need it.
if [ ! -f "php-${VERSION}.tar.gz" ]; then
    RESET=1
fi;

# Retrieve source code 
if [ $RESET -eq 1 ] ; then
    echo "Downloading php-${VERSION}.tar.gz"
    RESPONSE=$(curl --write-out %{http_code} --silent  --head --output /dev/null http://museum.php.net/php5/php-${VERSION}.tar.gz)
    echo $RESPONSE
    if [ $RESPONSE -eq 404 ]; then
        wget -O php-${VERSION}.tar.gz http://uk3.php.net/get/php-${VERSION}.tar.gz/from/this/mirror
    else
        wget http://museum.php.net/php5/php-${VERSION}.tar.gz
    fi

    if [ ! -f php-${VERSION}.tar.gz ];
    then
        echo "Could not find php-${VERSION}.tar.gz"
        exit;
    fi
    
    rm -rf ${PHP_DIR}
    tar -zxf php-${VERSION}.tar.gz
fi

cd $PHP_DIR
echo "Configuring ${VERSION}${POSTFIX} in $PHP_DIR"

# Configure 
OPTIONS="--with-gd --with-jpeg-dir=/usr --with-xpm-dir=/usr --with-freetype-dir=/usr \
    --with-mysql=/usr --enable-bcmath --with-gmp --with-readline \
    --with-openssl --with-curl --without-esmtp \
    --with-mysqli --enable-pcntl \
    --enable-memory-limit --with-mcrypt --with-t1lib \
    --enable-debug --with-iconv --enable-wddx --with-pdo-pgsql \
    --enable-spl --enable-pdo --with-pdo-mysql --with-pdo-sqlite \
    --with-ctype --with-bz2 --enable-mbstring --with-mime-magic \
    --with-xmlrpc --with-zlib --disable-zend-memory-manager --with-esmtp \
    --with-xsl --enable-exif --enable-soap --enable-ftp"

./configure --prefix=/usr/local/php/${VERSION}${POSTFIX} ${EXTRA_FLAGS} ${OPTIONS}

# Build and install
echo "Building ${VERSION}${POSTFIX} in $PHP_DIR"
make -j 5

echo "Installing ${VERSION}${POSTFIX} in $PHP_DIR"
sudo make install

echo "Linking PHPUnit library"
sudo ln -s /usr/share/php/PHPUnit /usr/local/php/${VERSION}${POSTFIX}/lib/php/PHPUnit

echo ""
echo "PHP version ${VERSION} is now installed. Type: pe ${VERSION}"
echo ""
