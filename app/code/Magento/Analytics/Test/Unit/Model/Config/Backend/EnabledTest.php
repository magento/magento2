<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Model\Config\Backend;


use Magento\Analytics\Model\Config\Backend\Enabled;
use Magento\Config\Model\ResourceModel\Config\Data;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Flag;
use Magento\Framework\FlagFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class EnabledTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ValueFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configValueFactoryMock;

    /**
     * @var Value|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configValueMock;

    /**
     * @var FlagFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flagFactoryMock;

    /**
     * @var Flag|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flagMock;

    /**
     * @var Flag\FlagResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flagResourceMock;

    /**
     * @var Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configValueResourceMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Enabled
     */
    private $enabledModel;

    /**
     * @var int
     */
    private $attemptsInitValue = 10;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->configValueFactoryMock = $this->getMockBuilder(ValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configValueMock = $this->getMockBuilder(Value::class)
            ->disableOriginalConstructor()
            ->setMethods(['setValue', 'setPath'])
            ->getMock();

        $this->flagFactoryMock = $this->getMockBuilder(FlagFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->flagMock = $this->getMockBuilder(Flag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->flagResourceMock = $this->getMockBuilder(Flag\FlagResource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configValueResourceMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->enabledModel = $this->objectManagerHelper->getObject(
            Enabled::class,
            [
                'flagFactory' => $this->flagFactoryMock,
                'configValueFactory' => $this->configValueFactoryMock,
                'flagResource' => $this->flagResourceMock,
                'configValueResource' => $this->configValueResourceMock,
                'attemptsInitValue' => $this->attemptsInitValue,
            ]
        );
    }

    /**
     * @return void
     */
    public function testAfterSaveSuccess()
    {
        $this->enabledModel->setValue('new');

        $this->configValueFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->configValueMock);
        $this->configValueResourceMock
            ->expects($this->once())
            ->method('load')
            ->with($this->configValueMock, Enabled::CRON_STRING_PATH, 'path')
            ->willReturnSelf();
        $this->configValueMock
            ->expects($this->once())
            ->method('setValue')
            ->willReturnSelf();
        $this->configValueMock
            ->expects($this->once())
            ->method('setPath')
            ->with(Enabled::CRON_STRING_PATH)
            ->willReturnSelf();
        $this->configValueResourceMock
            ->expects($this->once())
            ->method('save')
            ->with($this->configValueMock)
            ->willReturnSelf();

        $this->flagFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(['data' => ['flag_code' => Enabled::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE]])
            ->willReturn($this->flagMock);
        $this->flagMock
            ->expects($this->once())
            ->method('loadSelf')
            ->willReturnSelf();
        $this->flagMock
            ->expects($this->once())
            ->method('setFlagData')
            ->with($this->attemptsInitValue)
            ->willReturnSelf();

        $this->flagResourceMock
            ->expects($this->once())
            ->method('save')
            ->with($this->flagMock)
            ->willReturnSelf();

        $this->assertInstanceOf(
            Value::class,
            $this->enabledModel->afterSave()
        );
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testExecuteAfterSaveFailedWithLocalizedException()
    {
        $this->enabledModel->setValue('new');

        $this->configValueFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception('Message'));

        $this->enabledModel->afterSave();
    }
}
