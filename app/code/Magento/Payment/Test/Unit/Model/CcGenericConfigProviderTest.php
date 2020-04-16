<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Model;

use Magento\Payment\Helper\Data;
use Magento\Payment\Model\CcConfig;
use Magento\Payment\Model\CcGenericConfigProvider;

class CcGenericConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var CcGenericConfigProvider */
    protected $model;

    /** @var CcConfig|\PHPUnit\Framework\MockObject\MockObject */
    protected $ccConfigMock;

    /** @var Data|\PHPUnit\Framework\MockObject\MockObject */
    protected $paymentHelperMock;

    protected function setUp(): void
    {
        $this->ccConfigMock = $this->createMock(\Magento\Payment\Model\CcConfig::class);
        $this->paymentHelperMock = $this->createMock(\Magento\Payment\Helper\Data::class);

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
