<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model\Config\Backend;

use Magento\Analytics\Model\Config\Backend\CollectionTime;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CollectionTimeTest extends TestCase
{
    /**
     * @var WriterInterface|MockObject
     */
    private $configWriterMock;

    /**
     * @var LoggerInterface|MockObject
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
    protected function setUp(): void
    {
        $this->configWriterMock = $this->getMockForAbstractClass(WriterInterface::class);

        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

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
     */
    public function testAfterSaveWrongValue()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->collectionTime->setData('value', '00,01');
        $this->collectionTime->afterSave();
    }

    /**
     * @return void
     */
    public function testAfterSaveWithLocalizedException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
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
