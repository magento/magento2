<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Config\Backend;

use Magento\Analytics\Model\Config\Backend\CollectionTime;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Psr\Log\LoggerInterface;

/**
 * Class CollectionTimeTest
 */
class CollectionTimeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var WriterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configWriterMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var CollectionTime
     */
    private $collectionTime;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->configWriterMock = $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->collectionTime = $this->objectManagerHelper->getObject(
            CollectionTime::class,
            [
                'configWriter' => $this->configWriterMock,
                '_logger' => $this->loggerMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testAfterSave()
    {
        $this->collectionTime->setData('value', '05,04,03');

        $this->configWriterMock
            ->expects($this->once())
            ->method('save')
            ->with(CollectionTime::CRON_SCHEDULE_PATH, join(' ', ['04', '05', '*', '*', '*']));

        $this->assertInstanceOf(
            Value::class,
            $this->collectionTime->afterSave()
        );
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testAfterSaveWrongValue()
    {
        $this->collectionTime->setData('value', '00,01');
        $this->collectionTime->afterSave();
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testAfterSaveWithLocalizedException()
    {
        $exception = new \Exception('Test message');
        $this->collectionTime->setData('value', '05,04,03');

        $this->configWriterMock
            ->expects($this->once())
            ->method('save')
            ->with(CollectionTime::CRON_SCHEDULE_PATH, join(' ', ['04', '05', '*', '*', '*']))
            ->willThrowException($exception);
        $this->loggerMock
            ->expects($this->once())
            ->method('error')
            ->with($exception->getMessage());
        $this->collectionTime->afterSave();
    }
}
