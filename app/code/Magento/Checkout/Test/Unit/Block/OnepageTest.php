<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block;

class OnepageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Checkout\Block\Onepage
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configProviderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $formKeyMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutProcessorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $contextMock = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->formKeyMock = $this->createMock(\Magento\Framework\Data\Form\FormKey::class);
        $this->configProviderMock = $this->createMock(\Magento\Checkout\Model\CompositeConfigProvider::class);

        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $contextMock->expects($this->once())->method('getStoreManager')->willReturn($this->storeManagerMock);
        $this->layoutProcessorMock = $this->createMock(
            \Magento\Checkout\Block\Checkout\LayoutProcessorInterface::class
        );

        $this->serializerMock = $this->createMock(\Magento\Framework\Serialize\Serializer\JsonHexTag::class);

        $this->model = new \Magento\Checkout\Block\Onepage(
            $contextMock,
            $this->formKeyMock,
            $this->configProviderMock,
            [$this->layoutProcessorMock],
            [],
            $this->serializerMock,
            $this->serializerMock
        );
    }

    public function testGetBaseUrl()
    {
        $baseUrl = 'http://magento.com';
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);

        $storeMock->expects($this->once())->method('getBaseUrl')->willReturn($baseUrl);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);

        $this->assertEquals($baseUrl, $this->model->getBaseUrl());
    }

    public function testGetCheckoutConfig()
    {
        $checkoutConfig = ['checkout', 'config'];
        $this->configProviderMock->expects($this->once())->method('getConfig')->willReturn($checkoutConfig);

        $this->assertEquals($checkoutConfig, $this->model->getCheckoutConfig());
    }

    public function testGetFormKey()
    {
        $formKey = 'form_key';
        $this->formKeyMock->expects($this->once())->method('getFormKey')->willReturn($formKey);

        $this->assertEquals($formKey, $this->model->getFormKey());
    }

    public function testGetJsLayout()
    {
        $processedLayout = ['layout' => ['processed' => true]];
        $jsonLayout = '{"layout":{"processed":true}}';
        $this->layoutProcessorMock->expects($this->once())->method('process')->with([])->willReturn($processedLayout);
        $this->serializerMock->expects($this->once())->method('serialize')->willReturn($jsonLayout);

        $this->assertEquals($jsonLayout, $this->model->getJsLayout());
    }

    public function testGetSerializedCheckoutConfig()
    {
        $checkoutConfig = ['checkout', 'config'];
        $this->configProviderMock->expects($this->once())->method('getConfig')->willReturn($checkoutConfig);
        $this->serializerMock->expects($this->once())->method('serialize')->willReturn(json_encode($checkoutConfig));

        $this->assertEquals(json_encode($checkoutConfig), $this->model->getSerializedCheckoutConfig());
    }
}
