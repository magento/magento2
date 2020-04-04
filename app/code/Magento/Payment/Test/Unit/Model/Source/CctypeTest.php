<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model\Source;

use Magento\Payment\Model\Config;
use Magento\Payment\Model\Source\Cctype;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CctypeTest extends TestCase
{
    /**
     * Payment config model
     *
     * @var Config|MockObject
     */
    private $paymentConfigMock;

    /**
     * @var Cctype
     */
    private $model;

    /**
     * List of allowed Cc types
     *
     * @var array
     */
    private $allowedTypes = ['allowed_cc_type'];

    /**
     * Cc type array
     *
     * @var array
     */
    private $cctypesArray = ['allowed_cc_type' => 'name'];

    /**
     * Expected cctype array after toOptionArray call
     *
     * @var array
     */
    private $expectedToOptionsArray = [['value' => 'allowed_cc_type', 'label' => 'name']];

    protected function setUp()
    {
        $this->paymentConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->model = new Cctype($this->paymentConfigMock);
    }

    public function testSetAndGetAllowedTypes()
    {
        $model = $this->model->setAllowedTypes($this->allowedTypes);
        $this->assertEquals($this->allowedTypes, $model->getAllowedTypes());
    }

    public function testToOptionArrayEmptyAllowed()
    {
        $this->_preparePaymentConfig();
        $this->assertEquals($this->expectedToOptionsArray, $this->model->toOptionArray());
    }

    public function testToOptionArrayNotEmptyAllowed()
    {
        $this->_preparePaymentConfig();
        $this->model->setAllowedTypes($this->allowedTypes);
        $this->assertEquals($this->expectedToOptionsArray, $this->model->toOptionArray());
    }

    private function _preparePaymentConfig()
    {
        $this->paymentConfigMock->expects($this->once())->method('getCcTypes')->will(
            $this->returnValue($this->cctypesArray)
        );
    }
}
