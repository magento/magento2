<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Model\Cart;

/**
 * Test for Magento\Checkout\Model\Cart\ConfigProvider class.
 */
class ConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Checkout\Model\Cart\ConfigProvider
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cartConfigProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $compositeConfigProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    protected function setUp()
    {
        $this->cartConfigProvider = $this->createMock(\Magento\Checkout\Model\Cart\ConfigProvider::class);
        $this->compositeConfigProvider = $this->createMock(\Magento\Checkout\Model\CompositeConfigProvider::class);
        $this->serializer = $this->createMock(\Magento\Framework\Serialize\SerializerInterface::class);

        $this->model = new \Magento\Checkout\Model\Cart\ConfigProvider(
            $this->compositeConfigProvider,
            $this->serializer
        );
    }

    public function testGetCheckoutConfig()
    {
        $config = ['param' => 'value'];
        $this->compositeConfigProvider
            ->expects($this->once())
            ->method('getConfig')
            ->willReturn($config);

        $this->assertEquals($config, $this->model->getCheckoutConfig());
    }

    public function testGetSerializedCheckoutConfig()
    {
        $config = '{"param":"value"}';

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->willReturn($config);

        $this->assertEquals($config, $this->model->getSerializedCheckoutConfig());
    }
}
