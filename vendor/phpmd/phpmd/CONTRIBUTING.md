How to Contribute
=================

The PHPMD project welcomes your contribution. There are several ways to help out:

* Create an [issue](https://github.com/phpmd/phpmd/issues/) on GitHub,
if you have found a bug or have an idea for a feature
* Write test cases for open bug issues
* Write patches for open bug/feature issues
* Participate on the PHPMD IRC Channel

There are a few guidelines that we need contributors to follow, so that we have a
chance of keeping on top of things.

* The code must follow the [PSR-2 coding standard](http://www.php-fig.org/psr/psr-2/).
* All code changes should be covered by unit tests

Issues
------

* Submit an [issue](https://github.com/phpmd/phpmd/issues/)
  * Make sure it does not already exist.
  * Clearly describe the issue including steps to reproduce, when it is a bug.
  * Make sure you note the PHPMD version you use.

Coding Standard
---------------

Make sure your code changes comply with the PSR-2 coding standard by
using [PHP Codesniffer](https://github.com/squizlabs/PHP_CodeSniffer)
from within your PHPMD folder:

    vendor/bin/phpcs -p --extensions=php --standard=PSR2 src > phpcs.txt

Check the ``phpcs.txt`` once it finished.

Additional Resources
--------------------

* [Existing issues](https://github.com/phpmd/phpmd/issues/)
* [General GitHub documentation](https://help.github.com/)
* [GitHub pull request documentation](https://help.github.com/send-pull-requests/)
* [PHPMD IRC Channel on freenode.org](http://webchat.freenode.net/?channels=phpmd)
