<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Block;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Review\Block\Form;
use Magento\Review\Helper\Data;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\Rating;
use Magento\Review\Model\ResourceModel\Rating\Collection as RatingCollection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends TestCase
{
    /**
     * @var Form
     */
    protected $object;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var Data|MockObject
     */
    protected $reviewDataMock;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    protected $productRepository;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilder;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->storeManager = $this->createPartialMock(StoreManager::class, ['getStore']);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->reviewDataMock = $this->createMock(Data::class);

        $this->reviewDataMock->expects($this->once())
            ->method('getIsGuestAllowToWrite')
            ->willReturn(true);

        $this->urlBuilder = $this->createMock(UrlInterface::class);
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
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);

        $this->serializerMock = $this->createMock(Json::class);

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

        $productMock = $this->createMock(ProductInterface::class);
        $this->productRepository->expects($this->once())
            ->method('getById')
            ->with($productId, false, $storeId)
            ->willReturn($productMock);

        $this->assertSame($productMock, $this->object->getProductInfo());
    }

    public function testGetProductInfoNonIntParam()
    {
        $productId = 3;
        $productIdNonInt = "3abc";
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
            ->willReturn($productIdNonInt);

        $productMock = $this->createMock(ProductInterface::class);
        $this->productRepository->expects($this->once())
            ->method('getById')
            ->with($productId, false, $storeId)
            ->willReturn($productMock);

        $this->assertSame($productMock, $this->object->getProductInfo());
    }

    /**
     * @param bool   $isSecure
     * @param string $actionUrl
     * @param int    $productId
     */
    #[DataProvider('getActionDataProvider')]
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

    public function testGetRatingsReturnsPreparedCollection(): void
    {
        $storeId = 10;

        $store = new DataObject(['id' => $storeId]);

        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $ratingCollection = $this->createMock(RatingCollection::class);
        $ratingCollection->expects($this->once())
            ->method('addEntityFilter')
            ->with('product')
            ->willReturnSelf();
        $ratingCollection->expects($this->once())
            ->method('setPositionOrder')
            ->willReturnSelf();
        $ratingCollection->expects($this->once())
            ->method('addRatingPerStoreName')
            ->with($storeId)
            ->willReturnSelf();
        $ratingCollection->expects($this->once())
            ->method('setStoreFilter')
            ->with($storeId)
            ->willReturnSelf();
        $ratingCollection->expects($this->once())
            ->method('setActiveFilter')
            ->with(true)
            ->willReturnSelf();
        $ratingCollection->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $ratingCollection->expects($this->once())
            ->method('addOptionToItems')
            ->willReturnSelf();

        $rating = $this->createMock(Rating::class);
        $rating->method('getResourceCollection')->willReturn($ratingCollection);

        $ratingFactory = $this->createMock(RatingFactory::class);
        $ratingFactory->method('create')->willReturn($rating);

        $formBlock = $this->getFormBlockWithInjectedDependencies($storeManager, $ratingFactory);

        $result = $formBlock->getRatings();

        $this->assertSame($ratingCollection, $result);
    }

    public function testGetRegisterUrl(): void
    {
        $expectedUrl = 'https://example.com/customer/account/create/';

        $customerUrl = $this->createPartialMock(Url::class, ['getRegisterUrl']);

        $customerUrl->expects($this->once())
            ->method('getRegisterUrl')
            ->willReturn($expectedUrl);

        $formBlock = $this->getFormBlockWithInjectedDependencies(
            $this->storeManager,
            $this->createMock(RatingFactory::class)
        );
        $this->setProtectedProperty($formBlock, 'customerUrl', $customerUrl);

        $this->assertSame($expectedUrl, $formBlock->getRegisterUrl());
    }

    public function testConstructSetsLoginLinkWhenGuestCannotWrite(): void
    {
        $currentUrl = 'https://example.com/current';
        $encoded = 'ENCODED_REFERER';
        $loginUrlBase = 'https://example.com/customer/account/login/';

        $urlBuilder = $this->createMock(UrlInterface::class);
        $urlBuilder->method('getUrl')
            ->willReturnCallback(
                function ($route, $params = []) use ($currentUrl, $encoded, $loginUrlBase) {
                    if ($route === '*/*/*') {
                        $this->assertSame(['_current' => true], $params);
                        return $currentUrl;
                    }
                    if ($route === 'customer/account/login/') {
                        $this->assertArrayHasKey(Url::REFERER_QUERY_PARAM_NAME, $params);
                        $this->assertSame($encoded, $params[Url::REFERER_QUERY_PARAM_NAME]);
                        return $loginUrlBase . '?' . Url::REFERER_QUERY_PARAM_NAME . '=' . $encoded;
                    }
                    return '';
                }
            );

        $urlEncoder = $this->createMock(EncoderInterface::class);
        $urlEncoder->expects($this->once())
            ->method('encode')
            ->with($currentUrl . '#review-form')
            ->willReturn($encoded);

        $httpContext = $this->createMock(HttpContext::class);
        $httpContext->method('getValue')
            ->with(CustomerContext::CONTEXT_AUTH)
            ->willReturn(false);

        $reviewData = $this->createMock(Data::class);
        $reviewData->method('getIsGuestAllowToWrite')->willReturn(false);

        $formBlock = $this->createPartialMock(Form::class, []);

        $this->setProtectedProperty($formBlock, '_urlBuilder', $urlBuilder);
        $this->setProtectedProperty($formBlock, 'urlEncoder', $urlEncoder);
        $this->setProtectedProperty($formBlock, 'httpContext', $httpContext);
        $this->setProtectedProperty($formBlock, '_reviewData', $reviewData);

        $ref = new \ReflectionObject($formBlock);
        $method = $ref->getMethod('_construct');
        $method->invoke($formBlock);

        $expectedLoginUrl = $loginUrlBase . '?' . Url::REFERER_QUERY_PARAM_NAME . '=' . $encoded;
        $this->assertSame($expectedLoginUrl, $formBlock->getLoginLink());
    }

    private function getFormBlockWithInjectedDependencies(
        StoreManagerInterface $storeManager,
        RatingFactory $ratingFactory
    ): Form {
        $formBlock = $this->createPartialMock(Form::class, []);

        // Inject protected properties via reflection to avoid full framework context construction
        $this->setProtectedProperty($formBlock, '_storeManager', $storeManager);
        $this->setProtectedProperty($formBlock, '_ratingFactory', $ratingFactory);

        return $formBlock;
    }

    private function setProtectedProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new \ReflectionObject($object);
        while ($reflection && !$reflection->hasProperty($property)) {
            $reflection = $reflection->getParentClass();
            if ($reflection === false) {
                $this->fail('Property ' . $property . ' not found in class hierarchy');
            }
        }
        $prop = $reflection->getProperty($property);
        $prop->setValue($object, $value);
    }
}
