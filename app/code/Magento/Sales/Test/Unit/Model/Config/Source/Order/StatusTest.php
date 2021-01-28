<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Config\Source\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class StatusTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Sales\Model\Config\Source\Order\Status */
    protected $object;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    /** @var \Magento\Sales\Model\Order\Config|\PHPUnit\Framework\MockObject\MockObject */
    protected $config;

    protected function setUp(): void
    {
        $this->config = $this->createMock(\Magento\Sales\Model\Order\Config::class);

        $this->objectManager = new ObjectManager($this);
        $this->object = $this->objectManager->getObject(
            \Magento\Sales\Model\Config\Source\Order\Status::class,
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
