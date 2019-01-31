<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Block;

use Magento\AuthorizenetAcceptjs\Block\Payment;
use Magento\AuthorizenetAcceptjs\Model\Ui\ConfigProvider;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    /**
     * @var ConfigProvider|MockObject|InvocationMocker
     */
    private $configMock;

    /**
     * @var Payment
     */
    private $block;

    protected function setUp()
    {
        $contextMock = $this->createMock(Context::class);
        $this->configMock = $this->createMock(ConfigProvider::class);
        $this->block = new Payment($contextMock, $this->configMock, new Json());
    }

    public function testConfigIsCreated()
    {
        $this->configMock->method('getConfig')
            ->willReturn([
                'payment' => [
                    'authorizenet_acceptjs' => [
                        'foo' => 'bar'
                    ]
                ]
            ]);

        $result = $this->block->getPaymentConfig();
        $expected = '{"foo":"bar","code":"authorizenet_acceptjs"}';
        $this->assertEquals($expected, $result);
    }
}
