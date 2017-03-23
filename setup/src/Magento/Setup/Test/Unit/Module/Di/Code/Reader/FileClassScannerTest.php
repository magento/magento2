<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Module\Di\Code\Reader;

use Magento\Setup\Module\Di\Code\Reader\FileClassScanner;
use Magento\Setup\Module\Di\Code\Reader\InvalidFileException;
use Magento\Setup\Test\Unit\Module\Di\Compiler\Config\Chain\PreferencesResolvingTest;

class FileClassScannerTest extends \PHPUnit_Framework_TestCase
{

    public function testInvalidFileThrowsException()
    {
        $this->setExpectedException(InvalidFileException::class);
        new FileClassScanner(false);
    }

    public function testEmptyArrayForFileWithoutNamespaceOrClass()
    {
        $scanner = $this->getMockBuilder(FileClassScanner::class)->disableOriginalConstructor()->setMethods([
            'getFileContents'
        ])->getMock();
        $scanner->expects(self::once())->method('getFileContents')->willReturn(<<<PHP
<?php

echo 'hello world';

if (class_exists('some_class')) {
    \$object = new some_class();
}
PHP
        );
        /* @var $scanner FileClassScanner */

        $result = $scanner->getClassNames();
        self::assertCount(0, $result);
    }

    public function testGetClassName()
    {
        $scanner = $this->getMockBuilder(FileClassScanner::class)->disableOriginalConstructor()->setMethods([
            'getFileContents'
        ])->getMock();
        $scanner->expects(self::once())->method('getFileContents')->willReturn(<<<PHP
<?php

class ThisIsATest {

}
PHP
        );
        /* @var $scanner FileClassScanner */

        $result = $scanner->getClassNames();

        self::assertCount(1, $result);
        self::assertContains('ThisIsATest', $result);
    }

    public function testGetClassNameAndSingleNamespace()
    {
        $scanner = $this->getMockBuilder(FileClassScanner::class)->disableOriginalConstructor()->setMethods([
            'getFileContents'
        ])->getMock();
        $scanner->expects(self::once())->method('getFileContents')->willReturn(<<<PHP
<?php

namespace NS;

class ThisIsMyTest {

}
PHP
        );
        /* @var $scanner FileClassScanner */

        $result = $scanner->getClassNames();

        self::assertCount(1, $result);
        self::assertContains('NS\ThisIsMyTest', $result);
    }

    public function testGetClassNameAndMultiNamespace()
    {
        $scanner = $this->getMockBuilder(FileClassScanner::class)->disableOriginalConstructor()->setMethods([
            'getFileContents'
        ])->getMock();
        $scanner->expects(self::once())->method('getFileContents')->willReturn(<<<PHP
<?php

namespace This\Is\My\Namespace;

class ThisIsMyTest {

    public function __construct()
    {
        \This\Is\Another\Namespace::class;
    }
    
    public function test()
    {
        
    }

}
PHP
        );
        /* @var $scanner FileClassScanner */

        $result = $scanner->getClassNames();

        self::assertCount(1, $result);
        self::assertContains('This\Is\My\Namespace\ThisIsMyTest', $result);
    }
    public function testGetMultipleClassesInMultiNamespace()
    {
        $scanner = $this->getMockBuilder(FileClassScanner::class)->disableOriginalConstructor()->setMethods([
            'getFileContents'
        ])->getMock();
        $scanner->expects(self::once())->method('getFileContents')->willReturn(<<<PHP
<?php

namespace This\Is\My\Namespace;

class ThisIsMyTest {

}

class ThisIsAnotherTest {

    public function __construct()
    {
    
    }
    
    public function test()
    {
        return self::class;
    }

}
PHP
        );
        /* @var $scanner FileClassScanner */

        $result = $scanner->getClassNames();

        self::assertCount(2, $result);
        self::assertContains('This\Is\My\Namespace\ThisIsMyTest', $result);
        self::assertContains('This\Is\My\Namespace\ThisIsAnotherTest', $result);
    }


}
