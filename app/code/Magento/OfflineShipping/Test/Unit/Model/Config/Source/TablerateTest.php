<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Test\Unit\Model\Config\Source;

class TablerateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\OfflineShipping\Model\Config\Source\Tablerate
     */
    protected $model;

    /**
     * @var \Magento\OfflineShipping\Model\Carrier\Tablerate|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $carrierTablerateMock;

    protected function setUp(): void
    {
        $this->carrierTablerateMock = $this->getMockBuilder(\Magento\OfflineShipping\Model\Carrier\Tablerate::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCode'])
            ->getMock();

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $helper->getObject(
            \Magento\OfflineShipping\Model\Config\Source\Tablerate::class,
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
