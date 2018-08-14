<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit;

use Magento\Framework\FlagFactory;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\Flag;
use Magento\Framework\FlagManager;
use \PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Class FlagManagerTest
 */
class FlagManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FlagFactory|Mock
     */
    private $flagFactoryMock;

    /**
     * @var Flag|Mock
     */
    private $flagMock;

    /**
     * @var FlagResource|Mock
     */
    private $flagResourceMock;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->flagFactoryMock = $this->getMockBuilder(FlagFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flagResourceMock = $this->getMockBuilder(FlagResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flagMock = $this->getMockBuilder(Flag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->flagManager = new FlagManager(
            $this->flagFactoryMock,
            $this->flagResourceMock
        );
    }

    public function testGetFlagData()
    {
        $flagCode = 'flag';
        $this->setupFlagObject($flagCode);
        $this->flagMock->expects($this->once())
            ->method('getFlagData')
            ->willReturn(10);

        $this->assertEquals($this->flagManager->getFlagData($flagCode), 10);
    }

    public function testSaveFlag()
    {
        $flagCode = 'flag';
        $this->setupFlagObject($flagCode);
        $this->flagMock->expects($this->once())
            ->method('setFlagData')
            ->with(10);
        $this->flagResourceMock->expects($this->once())
            ->method('save')
            ->with($this->flagMock);

        $this->assertTrue(
            $this->flagManager->saveFlag($flagCode, 10)
        );
    }

    /**
     * @dataProvider flagExistDataProvider
     *
     * @param bool $isFlagExist
     */
    public function testDeleteFlag($isFlagExist)
    {
        $flagCode = 'flag';

        $this->setupFlagObject($flagCode);

        $this->flagMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn($isFlagExist);

        if ($isFlagExist) {
            $this->flagResourceMock
                ->expects($this->once())
                ->method('delete')
                ->with($this->flagMock);
        }

        $this->assertTrue(
            $this->flagManager->deleteFlag($flagCode)
        );
    }

    /**
     * @param $flagCode
     */
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

    /**
     * Provide variations of the flag existence.
     *
     * @return array
     */
    public function flagExistDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }
}
