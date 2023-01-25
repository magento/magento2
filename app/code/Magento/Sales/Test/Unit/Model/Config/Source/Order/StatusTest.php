<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Config\Source\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Config\Source\Order\Status;
use Magento\Sales\Model\Order\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    /** @var Status */
    protected $object;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var Config|MockObject */
    protected $config;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);

        $this->objectManager = new ObjectManager($this);
        $this->object = $this->objectManager->getObject(
            Status::class,
            ['orderConfig' => $this->config]
        );
    }

    public function testToOptionArray()
    {
        $this->config->expects($this->once())->method('getStateStatuses')
            ->willReturn(['status1', 'status2']);

        $this->assertEquals(
            [
                ['value' => '', 'label' => '-- Please Select --'],
                ['value' => 0, 'label' => 'status1'],
                ['value' => 1, 'label' => 'status2'],
            ],
            $this->object->toOptionArray()
        );
    }
}
