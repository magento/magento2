<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Checkout\Block\Onepage;
use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Serialize\Serializer\JsonHexTag;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OnepageTest extends TestCase
{
    /**
     * @var Onepage
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $configProviderMock;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    protected $formKeyMock;

    /**
     * @var MockObject
     */
    protected $layoutProcessorMock;

    /**
     * @var MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $contextMock = $this->createMock(Context::class);
        $this->formKeyMock = $this->createMock(FormKey::class);
        $this->configProviderMock = $this->createMock(CompositeConfigProvider::class);

        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $contextMock->expects($this->once())->method('getStoreManager')->willReturn($this->storeManagerMock);
        $this->layoutProcessorMock = $this->createMock(
            LayoutProcessorInterface::class
        );

        $this->serializerMock = $this->createMock(JsonHexTag::class);

        $this->model = new Onepage(
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
        $storeMock = $this->createMock(Store::class);

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
