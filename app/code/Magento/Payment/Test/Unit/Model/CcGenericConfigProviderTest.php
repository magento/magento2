<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model;

use Magento\Payment\Helper\Data;
use Magento\Payment\Model\CcConfig;
use Magento\Payment\Model\CcGenericConfigProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CcGenericConfigProviderTest extends TestCase
{
    /**
     * @var CcGenericConfigProvider
     */
    private $model;

    /**
     * @var CcConfig|MockObject
     */
    private $ccConfigMock;

    /**
     * @var Data|MockObject
     */
    private $paymentHelperMock;

    protected function setUp()
    {
        $this->ccConfigMock = $this->createMock(CcConfig::class);
        $this->paymentHelperMock = $this->createMock(Data::class);

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
