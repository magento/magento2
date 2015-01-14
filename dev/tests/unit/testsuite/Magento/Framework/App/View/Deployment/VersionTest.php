<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\View\Deployment;


class VersionTest extends \PHPUnit_Framework_TestCase
{
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
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTime;

    protected function setUp()
    {
        $this->appState = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $this->versionStorage = $this->getMock('Magento\Framework\App\View\Deployment\Version\StorageInterface');
        $this->dateTime = $this->getMock('Magento\Framework\Stdlib\DateTime');
        $this->object = new Version($this->appState, $this->versionStorage, $this->dateTime);
    }

    public function testGetValueDeveloperMode()
    {
        $this->appState
            ->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue(\Magento\Framework\App\State::MODE_DEVELOPER));
        $this->versionStorage->expects($this->never())->method($this->anything());
        $this->dateTime->expects($this->once())->method('toTimestamp')->will($this->returnValue('123'));
        $this->assertEquals('123', $this->object->getValue());
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
        $this->dateTime->expects($this->never())->method('toTimestamp');
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
        $this->dateTime->expects($this->once())->method('toTimestamp')->will($this->returnValue('123'));
        $this->versionStorage->expects($this->once())->method('save')->with('123');
        $this->assertEquals('123', $this->object->getValue());
        $this->object->getValue(); // Ensure caching in memory
    }
}
