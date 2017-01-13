<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\FlagManager;
use Magento\Framework\FlagFactory;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\Flag;

/**
 * Class FlagManagerTest
 */
class FlagManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FlagFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flagFactoryMock;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var Flag|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flagMock;

    /**
     * @var FlagResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flagResourceMock;

    protected function setUp()
    {
        $this->flagFactoryMock =  $this->getMockBuilder(FlagFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flagResourceMock =  $this->getMockBuilder(FlagResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flagMock =  $this->getMockBuilder(Flag::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flagManager = new FlagManager(
            $this->flagFactoryMock,
            $this->flagResourceMock
        );
    }

    public function testGetFlagData()
    {
        $flagCode = "flag";
        $this->setupFlagObject($flagCode);
        $this->flagMock->expects($this->once())
            ->method('getFlagData')
            ->willReturn(10);
        $this->assertEquals($this->flagManager->getFlagData($flagCode), 10);
    }

    public function testSaveFlag()
    {
        $flagCode = "flag";
        $this->setupFlagObject($flagCode);
        $this->flagMock->expects($this->once())
            ->method('setFlagData')
            ->with(10);
        $this->flagResourceMock->expects($this->once())
            ->method('save')
            ->with($this->flagMock);
        $this->assertTrue($this->flagManager->saveFlag($flagCode, 10));
    }

    public function testDeleteFlag()
    {
        $flagCode = "flag";
        $this->setupFlagObject($flagCode);
        $this->flagResourceMock->expects($this->once())
            ->method('delete')
            ->with($this->flagMock);
        $this->assertTrue($this->flagManager->deleteFlag($flagCode));
    }

    private function setupFlagObject($flagCode)
    {
        $this->flagFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => ['flag_code' => $flagCode]])
            ->willReturn($this->flagMock);
        $this->flagResourceMock->expects($this->once())
            ->method('load')
            ->with($this->flagMock, $flagCode, 'flag_code');
    }
}
