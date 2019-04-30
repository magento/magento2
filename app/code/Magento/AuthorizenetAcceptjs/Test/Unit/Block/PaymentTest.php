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
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for Magento\AuthorizenetAcceptjs\Test\Unit\Block\Payment
 */
class PaymentTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var ConfigProvider|MockObject
     */
    private $configMock;

    /**
     * @var Payment
     */
    private $block;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->configMock = $this->createMock(ConfigProvider::class);
        $contextMock = $this->createMock(Context::class);

        $this->block = $this->objectManagerHelper->getObject(
            Payment::class,
            [
                'context' => $contextMock,
                'config' => $this->configMock,
                'json' => new Json(),
            ]
        );
    }

    /**
     * @return void
     */
    public function testConfigIsCreated()
    {
        $this->configMock->method('getConfig')
            ->willReturn([
                'payment' => [
                    'authorizenet_acceptjs' => [
                        'foo' => 'bar',
                    ],
                ],
            ]);

        $result = $this->block->getPaymentConfig();
        $expected = '{"foo":"bar","code":"authorizenet_acceptjs"}';
        $this->assertEquals($expected, $result);
    }
}
