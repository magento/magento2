<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Test\Unit\Model\Config\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OfflineShipping\Model\Config\Source\Tablerate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TablerateTest extends TestCase
{
    /**
     * @var Tablerate
     */
    protected $model;

    /**
     * @var \Magento\OfflineShipping\Model\Carrier\Tablerate|MockObject
     */
    protected $carrierTablerateMock;

    protected function setUp(): void
    {
        $this->carrierTablerateMock = $this->getMockBuilder(\Magento\OfflineShipping\Model\Carrier\Tablerate::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode'])
            ->getMock();

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            Tablerate::class,
            ['carrierTablerate' => $this->carrierTablerateMock]
        );
    }

    public function testToOptionArray()
    {
        $codes = [1, 2, 3, 4, 5];
        $expected = [];
        foreach ($codes as $k => $v) {
            $expected[] = ['value' => $k, 'label' => $v];
        }

        $this->carrierTablerateMock->expects($this->once())
            ->method('getCode')
            ->willReturn($codes);

        $this->assertEquals($expected, $this->model->toOptionArray());
    }
}
