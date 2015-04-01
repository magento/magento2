<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\View\Deployment;

use \Magento\Framework\App\View\Deployment\Version;


class VersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Current timestamp for test
     */
    const CURRENT_TIMESTAMP = 360;

    /**
     * @var Version
     */
    private $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $appState;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $versionStorage;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dateModel;

    protected function setUp()
    {
        $this->appState = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $this->versionStorage = $this->getMock('Magento\Framework\App\View\Deployment\Version\StorageInterface');
        $this->dateModel = $this->getMock('Magento\Framework\Stdlib\DateTime\DateTime', [], [], '', false);
        $this->object = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            'Magento\Framework\App\View\Deployment\Version',
            [
                'appState' => $this->appState,
                'versionStorage' => $this->versionStorage,
                'dateModel' => $this->dateModel
            ]
        );
    }

    public function testGetValueDeveloperMode()
    {
        $this->dateModel->expects($this->once())->method('gmtTimestamp')->willReturn(self::CURRENT_TIMESTAMP);
        $this->appState
            ->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue(\Magento\Framework\App\State::MODE_DEVELOPER));
        $this->versionStorage->expects($this->never())->method($this->anything());
        $this->assertEquals(self::CURRENT_TIMESTAMP, $this->object->getValue());
        $this->object->getValue(); // Ensure computation occurs only once and result is cached in memory
    }

    /**
     * @param string $appMode
     * @dataProvider getValueFromStorageDataProvider
     */
    public function testGetValueFromStorage($appMode)
    {
        $this->appState
            ->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue($appMode));
        $this->versionStorage->expects($this->once())->method('load')->will($this->returnValue('123'));
        $this->versionStorage->expects($this->never())->method('save');
        $this->assertEquals('123', $this->object->getValue());
        $this->object->getValue(); // Ensure caching in memory
    }

    public function getValueFromStorageDataProvider()
    {
        return [
            'default mode'      => [\Magento\Framework\App\State::MODE_DEFAULT],
            'production mode'   => [\Magento\Framework\App\State::MODE_PRODUCTION],
            'arbitrary mode'    => ['test'],
        ];
    }

    public function testGetValueDefaultModeSaving()
    {
        $this->appState
            ->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue(\Magento\Framework\App\State::MODE_DEFAULT));
        $storageException = new \UnexpectedValueException('Does not exist in the storage');
        $this->versionStorage
            ->expects($this->once())
            ->method('load')
            ->will($this->throwException($storageException));
        $this->dateModel->expects($this->once())->method('gmtTimestamp')->willReturn(self::CURRENT_TIMESTAMP);
        $this->versionStorage->expects($this->once())->method('save')->with(self::CURRENT_TIMESTAMP);
        $this->assertEquals(self::CURRENT_TIMESTAMP, $this->object->getValue());
        $this->object->getValue(); // Ensure caching in memory
    }
}
