<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\StartUpdater;

class StartUpdaterTest extends \PHPUnit_Framework_TestCase
{
    public static $stateType = 'cm';

    public static $type = 'update';

    public function testIndexAction()
    {
        $updater = $this->getMock('Magento\Setup\Model\Updater', [], [], '', false);
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        /** @var $controller StartUpdater */
        $controller = new StartUpdater($filesystem, $updater);
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testUpdateActionSuccessUpdate()
    {
        self::$type = 'update';
        self::$stateType = 'cm';
        $this->assertUpdateSuccess();
    }

    public function testUpdateActionSuccessUpgrade()
    {
        self::$type = 'upgrade';
        self::$stateType = 'su';
        $this->assertUpdateSuccess();
    }

    private function assertUpdateSuccess()
    {
        $updater = $this->getMock('Magento\Setup\Model\Updater', [], [], '', false);
        $updater->expects($this->once())
            ->method('createUpdaterTask')
            ->with([['name' => 'vendor/package', 'version' => '1.0']]);
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $directoryWrite = $this->getMock('Magento\Framework\Filesystem\Directory\Write', [], [], '', false);
        $directoryWrite->expects($this->once())->method('writeFile')->with('.type.json', self::$type);
        $filesystem->expects($this->once())->method('getDirectoryWrite')->willReturn($directoryWrite);
        /** @var $controller StartUpdater */
        $controller = new StartUpdater($filesystem, $updater);
        $jsonModel = $controller->updateAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $this->assertTrue($jsonModel->terminate());
    }
}

namespace Zend\Json;

use Magento\Setup\Test\Unit\Controller\StartUpdaterTest;

class Json
{
    const TYPE_ARRAY = 0;

    public static function decode()
    {
        return [
            'packages' => [['name' => 'vendor/package', 'version' => '1.0']],
            'type' => StartUpdaterTest::$stateType
        ];
    }

    public static function encode()
    {
        return StartUpdaterTest::$type;
    }
}
