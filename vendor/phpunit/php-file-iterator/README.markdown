File_Iterator
=============

Installation
------------

File_Iterator should be installed using the [PEAR Installer](http://pear.php.net/). This installer is the backbone of PEAR, which provides a distribution system for PHP packages, and is shipped with every release of PHP since version 4.3.0.

The PEAR channel (`pear.phpunit.de`) that is used to distribute File_Iterator needs to be registered with the local PEAR environment:

    sb@ubuntu ~ % pear channel-discover pear.phpunit.de
    Adding Channel "pear.phpunit.de" succeeded
    Discovery of channel "pear.phpunit.de" succeeded

This has to be done only once. Now the PEAR Installer can be used to install packages from the PHPUnit channel:

    sb@vmware ~ % pear install phpunit/File_Iterator
    downloading File_Iterator-1.1.1.tgz ...
    Starting to download File_Iterator-1.1.1.tgz (3,173 bytes)
    ....done: 3,173 bytes
    install ok: channel://pear.phpunit.de/File_Iterator-1.1.1

After the installation you can find the File_Iterator source files inside your local PEAR directory; the path is usually `/usr/lib/php/File`.
