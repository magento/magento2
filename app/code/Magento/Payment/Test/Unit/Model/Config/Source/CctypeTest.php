<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model\Config\Source;

use Magento\Payment\Model\Config;
use Magento\Payment\Model\Config\Source\Cctype;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CctypeTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $paymentConfigMock;

    /**
     * @var Cctype
     */
    private $model;

    protected function setUp()
    {
        $this->paymentConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->model = new Cctype($this->paymentConfigMock);
    }

    public function testToOptionArray()
    {
        $cctypesArray = ['code' => 'name'];
        $expectedArray = [
            ['value' => 'code', 'label' => 'name'],
        ];
        $this->paymentConfigMock
            ->expects($this->once())->method('getCcTypes')
            ->will($this->returnValue($cctypesArray));
        $this->assertEquals($expectedArray, $this->model->toOptionArray());
    }
}
