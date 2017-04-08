<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreStart
namespace Magento\Framework\Code\Generator {
    use Magento\Framework\Code\Test\Unit\Generator\DefinedClassesTest;

    function class_exists($className)
    {
        return DefinedClassesTest::$definedClassesTestActive
            ? $className === DefinedClassesTest::$classInMemory
            : \class_exists($className);
    }
}

namespace Magento\Framework\Code\Test\Unit\Generator {
    use Magento\Framework\Autoload\AutoloaderInterface;
    use Magento\Framework\Autoload\AutoloaderRegistry;
    use Magento\Framework\Code\Generator\DefinedClasses;

    // @codingStandardsIgnoreEnd

    class DefinedClassesTest extends \PHPUnit_Framework_TestCase
    {
        /** @var bool  */
        public static $definedClassesTestActive = false;

        public static $classInMemory = 'Class\That\Exists\In\Memory';

        /** @var  DefinedClasses */
        private $model;

        /** @var  AutoloaderInterface */
        private $initAutoloader;

        protected function setUp()
        {
            $this->model = new DefinedClasses();
            self::$definedClassesTestActive = true;
            $this->initAutoloader = AutoloaderRegistry::getAutoloader();
        }

        public function tearDown()
        {
            self::$definedClassesTestActive = false;
            AutoloaderRegistry::registerAutoloader($this->initAutoloader);
        }

        public function testClassLoadableFromMemory()
        {
            $this->assertTrue($this->model->isClassLoadable(self::$classInMemory));
        }

        public function testClassLoadableFromDisc()
        {
            $classOnDisc = 'Class\That\Exists\On\Disc';
            /**
             * @var AutoloaderInterface | \PHPUnit_Framework_MockObject_MockObject $autoloaderMock
             */
            $autoloaderMock = $this->getMock(\Magento\Framework\Autoload\AutoloaderInterface::class);
            $autoloaderMock->expects($this->once())->method('findFile')->with($classOnDisc)->willReturn(true);
            AutoloaderRegistry::registerAutoloader($autoloaderMock);
            $this->assertTrue($this->model->isClassLoadable($classOnDisc));
        }

        public function testClassNotLoadable()
        {
            $this->assertFalse($this->model->isClassLoadable('Class\Does\Not\Exist'));
        }
    }
}
