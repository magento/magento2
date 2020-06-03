<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model;

use Magento\Payment\Helper\Data;
use Magento\Payment\Model\CcConfig;
use Magento\Payment\Model\CcGenericConfigProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CcGenericConfigProviderTest extends TestCase
{
    /** @var CcGenericConfigProvider */
    protected $model;

    /** @var CcConfig|MockObject */
    protected $ccConfigMock;

    /** @var Data|MockObject */
    protected $paymentHelperMock;

    protected function setUp(): void
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
