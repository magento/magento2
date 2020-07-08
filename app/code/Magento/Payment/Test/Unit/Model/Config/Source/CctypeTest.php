<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Config\Source;

use Magento\Payment\Model\Config;
use Magento\Payment\Model\Config\Source\Cctype;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CctypeTest extends TestCase
{
    /**
     * Payment data
     *
     * @var Config|MockObject
     */
    protected $_paymentConfig;

    /**
     * @var Cctype
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_paymentConfig = $this->getMockBuilder(
            Config::class
        )->disableOriginalConstructor()
            ->setMethods([])->getMock();

        $this->_model = new Cctype($this->_paymentConfig);
    }

    public function testToOptionArray()
    {
        $cctypesArray = ['code' => 'name'];
        $expectedArray = [
            ['value' => 'code', 'label' => 'name'],
        ];
        $this->_paymentConfig->expects($this->once())->method('getCcTypes')->willReturn($cctypesArray);
        $this->assertEquals($expectedArray, $this->_model->toOptionArray());
    }
}
