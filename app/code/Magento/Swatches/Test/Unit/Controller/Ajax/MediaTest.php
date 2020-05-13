<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Test\Unit\Controller\Ajax;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\PageCache\Model\Config as PageCacheConfig;
use Magento\Swatches\Controller\Ajax\Media;
use Magento\Swatches\Helper\Data as SwatchesHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MediaTest extends TestCase
{
    private const STUB_CACHE_TTL = 1;
    private const STUB_PRODUCT_ID = 59;
    private const STUB_PRODUCT_ID_NOT_EXIST = 333;
    private const STUB_PRODUCT_IDENTITIES = ['stub', 'cache', 'identities'];

    /** @var array */
    private $mediaGalleryStub;

    /** @var MockObject|ProductRepositoryInterface */
    private $productRepositoryMock;

    /** @var MockObject|ResultJsonFactory */
    private $jsonResultFactoryMock;

    /** @var MockObject|SwatchesHelper */
    private $swatchHelperMock;

    /** @var MockObject|PageCacheConfig */
    private $cacheConfigMock;

    /** @var MockObject|Product */
    private $productMock;

    /** @var MockObject|RequestInterface */
    private $requestMock;

    /** @var MockObject|HttpResponse */
    private $responseMock;

    /** @var MockObject|ResultJson */
    private $jsonResultMock;

    /** @var Media */
    private $mediaAction;

    protected function setUp(): void
    {
        $this->mediaGalleryStub = [
            'image' => '/m/a/magento.png',
            'small_image' => '/m/a/magento.png',
            'thumbnail' => '/m/a/magento.png',
            'swatch_image' => '/m/a/magento.png',
        ];

        $this->cacheConfigMock = $this->createMock(PageCacheConfig::class);
        $this->cacheConfigMock->method('getTtl')->willReturn(self::STUB_CACHE_TTL);
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->responseMock = $this->getMockBuilder(HttpResponse::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPublicHeaders'])
            ->getMockForAbstractClass();
        $this->productMock = $this->createPartialMock(Product::class, ['getIdentities']);
        $this->jsonResultMock = $this->createMock(ResultJson::class);
        $this->jsonResultFactoryMock = $this->createMock(JsonFactory::class);
        $this->jsonResultFactoryMock->method('create')->willReturn($this->jsonResultMock);
        $this->swatchHelperMock = $this->createMock(SwatchesHelper::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->responseMock->method('setPublicHeaders')->willReturnSelf();

        $this->mediaAction = new Media(
            $this->requestMock,
            $this->productRepositoryMock,
            $this->responseMock,
            $this->jsonResultFactoryMock,
            $this->swatchHelperMock,
            $this->cacheConfigMock
        );
    }

    public function testExecuteReturnsEmptyJsonWhenProductIdNotProvided()
    {
        // Given
        $this->jsonResultFactoryMock->method('create')
            ->willReturn($this->jsonResultMock);
        $this->requestMock->method('getParam')
            ->with('product_id')
            ->willReturn(null);

        // Expect
        $this->swatchHelperMock->expects($this->never())
            ->method('getProductMediaGallery');
        $this->jsonResultMock->expects($this->once())
            ->method('setData')
            ->with([]);

        // When
        $this->mediaAction->execute();
    }

    public function testExecuteReturnsEmptyArrayWhenProductDoesNotExists()
    {
        // Given
        $this->jsonResultFactoryMock->method('create')
            ->willReturn($this->jsonResultMock);
        $this->requestMock->method('getParam')
            ->with('product_id')
            ->willReturn(self::STUB_PRODUCT_ID_NOT_EXIST);
        $this->productRepositoryMock->method('get')
            ->with(self::STUB_PRODUCT_ID_NOT_EXIST)
            ->willThrowException(new NoSuchEntityException(
                __("The product that was requested doesn't exist. Verify the product and try again.")
            ));

        // Expect
        $this->swatchHelperMock->expects($this->never())
            ->method('getProductMediaGallery');
        $this->jsonResultMock->expects($this->once())
            ->method('setData')
            ->with([]);

        // When
        $this->mediaAction->execute();
    }

    public function testExecuteReturnsProductMediaGalleryAsJsonData()
    {
        // Given
        $this->jsonResultFactoryMock->method('create')
            ->willReturn($this->jsonResultMock);
        $this->requestMock->method('getParam')
            ->with('product_id')
            ->willReturn(self::STUB_PRODUCT_ID);
        $this->productRepositoryMock->method('get')
            ->with(self::STUB_PRODUCT_ID)
            ->willReturn($this->productMock);
        $this->swatchHelperMock->expects($this->once())
            ->method('getProductMediaGallery')
            ->willReturn($this->mediaGalleryStub);
        $this->productMock->method('getIdentities')
            ->willReturn(self::STUB_PRODUCT_IDENTITIES);

        // Expect
        $this->jsonResultMock->expects($this->once())
            ->method('setData')
            ->with($this->mediaGalleryStub);

        // When
        $this->mediaAction->execute();
    }
}
