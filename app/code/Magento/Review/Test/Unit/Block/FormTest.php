<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Block;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Review\Block\Form;
use Magento\Review\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{
    /** @var Form */
    protected $object;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /** @var Context|MockObject */
    protected $context;

    /**
     * @var Data|MockObject
     */
    protected $reviewDataMock;

    /** @var ProductRepositoryInterface|MockObject */
    protected $productRepository;

    /** @var StoreManagerInterface|MockObject */
    protected $storeManager;

    /** @var UrlInterface|MockObject */
    protected $urlBuilder;

    /** @var Json|MockObject */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->reviewDataMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->reviewDataMock->expects($this->once())
            ->method('getIsGuestAllowToWrite')
            ->willReturn(true);

        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
        $this->context = $this->createMock(Context::class);
        $this->context->expects(
            $this->any()
        )->method(
            'getStoreManager'
        )->willReturn(
            $this->storeManager
        );
        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->context->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilder);
        $this->productRepository = $this->getMockForAbstractClass(ProductRepositoryInterface::class);

        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->object = $this->objectManagerHelper->getObject(
            Form::class,
            [
                'context' => $this->context,
                'reviewData' => $this->reviewDataMock,
                'productRepository' => $this->productRepository,
                'data' => [
                    'jsLayout' => [
                        'some-layout' => 'layout information'
                    ]
                ],
                'serializer' => $this->serializerMock
            ]
        );
    }

    public function testGetProductInfo()
    {
        $productId = 3;
        $storeId = 1;

        $this->storeManager->expects(
            $this->any()
        )->method(
            'getStore'
        )->willReturn(
            new DataObject(['id' => $storeId])
        );

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id', false)
            ->willReturn($productId);

        $productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $this->productRepository->expects($this->once())
            ->method('getById')
            ->with($productId, false, $storeId)
            ->willReturn($productMock);

        $this->assertSame($productMock, $this->object->getProductInfo());
    }

    /**
     * @param bool $isSecure
     * @param string $actionUrl
     * @param int $productId
     * @dataProvider getActionDataProvider
     */
    public function testGetAction($isSecure, $actionUrl, $productId)
    {
        $this->urlBuilder->expects($this->any())
            ->method('getUrl')
            ->with('review/product/post', ['_secure' => $isSecure, 'id' => $productId])
            ->willReturn($actionUrl . '/id/' . $productId);
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with('id', false)
            ->willReturn($productId);
        $this->requestMock->expects($this->any())
            ->method('isSecure')
            ->willReturn($isSecure);

        $this->assertEquals($actionUrl . '/id/' . $productId, $this->object->getAction());
    }

    /**
     * @return array
     */
    public static function getActionDataProvider()
    {
        return [
            [false, 'http://localhost/review/product/post', 3],
            [true, 'https://localhost/review/product/post' ,3],
        ];
    }

    public function testGetJsLayout()
    {
        $jsLayout = [
            'some-layout' => 'layout information'
        ];

        $this->serializerMock->expects($this->once())->method('serialize')
            ->willReturn(json_encode($jsLayout));
        $this->assertEquals('{"some-layout":"layout information"}', $this->object->getJsLayout());
    }
}
