<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Model;

use Magento\Payment\Helper\Data;
use Magento\Payment\Model\CcConfig;
use Magento\Payment\Model\CcGenericConfigProvider;

class CcGenericConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var CcGenericConfigProvider */
    protected $model;

    /** @var CcConfig|\PHPUnit_Framework_MockObject_MockObject */
    protected $ccConfigMock;

    /** @var Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $paymentHelperMock;

    protected function setUp()
    {
        $this->ccConfigMock = $this->getMock(\Magento\Payment\Model\CcConfig::class, [], [], '', false);
        $this->paymentHelperMock = $this->getMock(\Magento\Payment\Helper\Data::class, [], [], '', false);

        $this->model = new CcGenericConfigProvider(
            $this->ccConfigMock,
            $this->paymentHelperMock
        );
    }

    public function testGetConfig()
    {
        $this->assertEquals([], $this->model->getConfig());
    }
}
