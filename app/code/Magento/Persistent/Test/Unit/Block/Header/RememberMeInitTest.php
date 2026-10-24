<?php
/**
 * Copyright 2024 Adobe.
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Block\Header;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Persistent\Block\Header\RememberMeInit;
use Magento\Persistent\Model\CheckoutConfigProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RememberMeInitTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializer;

    /**
     * @var CheckoutConfigProvider|MockObject
     */
    private $checkoutConfigProvider;

    /**
     * @var RememberMeInit
     */
    private $rememberMeInit;

    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $eventManager = $this->createMock(ManagerInterface::class);
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->checkoutConfigProvider = $this->createMock(CheckoutConfigProvider::class);

        $this->context->method('getEventManager')->willReturn($eventManager);
        $this->context->method('getScopeConfig')->willReturn($scopeConfig);

        $this->rememberMeInit = new RememberMeInit(
            $this->context,
            [],
            $this->serializer,
            $this->checkoutConfigProvider
        );
    }
    public function testToHtml()
    {
        $config = ['key' => 'value'];
        $serializedConfig = json_encode($config);

        $this->checkoutConfigProvider->method('getConfig')->willReturn($config);
        $this->serializer->method('serialize')->with($config)->willReturn($serializedConfig);

        $expectedHtml = '<script type="text/x-magento-init">{"*":
            {"Magento_Persistent/js/remember-me-config": {
            "config": ' . $serializedConfig . '
            }}}</script>';
        $this->assertEquals($expectedHtml, $this->rememberMeInit->toHtml());
    }
}
