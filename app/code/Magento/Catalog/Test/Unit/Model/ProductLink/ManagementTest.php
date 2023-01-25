<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ProductLink;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Catalog\Model\ProductLink\Link;
use Magento\Catalog\Model\ProductLink\Management;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test for Magento\Catalog\Model\ProductLink\Management
 */
class ManagementTest extends TestCase
{
    const STUB_PRODUCT_SKU_1 = 'Simple Product 1';
    const STUB_PRODUCT_SKU_2 = 'Simple Product 2';
    const STUB_PRODUCT_TYPE = 'simple';
    const STUB_LINK_TYPE = 'related';
    const STUB_BAD_TYPE = 'bad type';

    /**
     * @var Management
     */
    protected $model;

    /**
     * @var ProductRepository|MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    /**
     * @var LinkTypeProvider|MockObject
     */
    protected $linkTypeProviderMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->productRepositoryMock = $this->createMock(ProductRepository::class);
        $this->productMock = $this->createMock(Product::class);
        $this->linkTypeProviderMock = $this->createMock(LinkTypeProvider::class);

        $this->objectManager = new ObjectManagerHelper($this);
        $this->model = $this->objectManager->getObject(
            Management::class,
            [
                'productRepository' => $this->productRepositoryMock,
                'linkTypeProvider' => $this->linkTypeProviderMock
            ]
        );
    }

    /**
     * Test getLinkedItemsByType()
     *
     * @return void
     */
    public function testGetLinkedItemsByType(): void
    {
        $productSku = self::STUB_PRODUCT_SKU_1;
        $linkType = self::STUB_LINK_TYPE;

        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);

        $links = $this->getInputRelatedLinkMock(
            $productSku,
            $linkType,
            self::STUB_PRODUCT_SKU_2,
            self::STUB_PRODUCT_TYPE
        );

        $this->getLinkTypesMock();

        $this->productMock->expects($this->once())
            ->method('getProductLinks')
            ->willReturn($links);

        $this->assertEquals(
            $links,
            $this->model->getLinkedItemsByType($productSku, $linkType)
        );
    }

    /**
     * Test for GetLinkedItemsByType() with wrong type
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function testGetLinkedItemsByTypeWithWrongType(): void
    {
        $productSku = self::STUB_PRODUCT_SKU_1;
        $linkType = self::STUB_BAD_TYPE;

        $this->productRepositoryMock->expects($this->never())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);

        $links = $this->getInputRelatedLinkMock(
            $productSku,
            $linkType,
            self::STUB_PRODUCT_SKU_2,
            self::STUB_PRODUCT_TYPE
        );

        $this->getLinkTypesMock();

        $this->productMock->expects($this->never())
            ->method('getProductLinks')
            ->willReturn($links);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage(
            'The "bad type" link type is unknown. Verify the type and try again.'
        );

        $this->model->getLinkedItemsByType($productSku, $linkType);
    }

    /**
     * Test for setProductLinks()
     *
     * @return void
     */
    public function testSetProductLinks(): void
    {
        $productSku = self::STUB_PRODUCT_SKU_1;
        $linkType = self::STUB_LINK_TYPE;

        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);

        $links = $this->getInputRelatedLinkMock(
            $productSku,
            $linkType,
            self::STUB_PRODUCT_SKU_2,
            self::STUB_PRODUCT_TYPE
        );

        $this->getLinkTypesMock();

        $this->productMock->expects($this->once())
            ->method('getProductLinks')
            ->willReturn([]);
        $this->productMock->expects($this->once())
            ->method('setProductLinks')
            ->with($links);

        $this->assertTrue($this->model->setProductLinks($productSku, $links));
    }

    /**
     * Test for SetProductLinks without link type in link object
     *
     * @return void
     * @throws InputException
     */
    public function testSetProductLinksWithoutLinkTypeInLink(): void
    {
        $productSku = self::STUB_PRODUCT_SKU_1;

        $inputRelatedLink = $this->objectManager->getObject(Link::class);
        $inputRelatedLink->setProductSku($productSku);
        $inputRelatedLink->setData("sku", self::STUB_PRODUCT_SKU_2);
        $inputRelatedLink->setPosition(0);
        $links = [$inputRelatedLink];

        $this->getLinkTypesMock();

        $this->expectException(InputException::class);
        $this->expectExceptionMessage(
            '"linkType" is required. Enter and try again.'
        );

        $this->assertTrue($this->model->setProductLinks($productSku, $links));
    }

    /**
     * Test for SetProductLinks with empty array of items
     *
     * @return void
     * @throws InputException
     */
    public function testSetProductLinksWithEmptyArrayItems(): void
    {
        $productSku = self::STUB_PRODUCT_SKU_1;

        $this->productRepositoryMock->expects($this->never())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);

        $this->linkTypeProviderMock->expects($this->never())
            ->method('getLinkTypes')
            ->willReturn([]);

        $this->expectException(InputException::class);
        $this->expectExceptionMessage(
            'Invalid value of "empty array" provided for the items field.'
        );

        $this->assertTrue($this->model->setProductLinks($productSku, []));
    }

    /**
     * Test setProductLinks() throw exception if product link type not exist
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function testSetProductLinksThrowExceptionIfProductLinkTypeDoesNotExist()
    {
        $productSku = self::STUB_PRODUCT_SKU_1;
        $linkType = self::STUB_BAD_TYPE;

        $this->productRepositoryMock->expects($this->never())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);

        $links = $this->getInputRelatedLinkMock(
            $productSku,
            $linkType,
            self::STUB_PRODUCT_SKU_2,
            self::STUB_PRODUCT_TYPE
        );

        $this->getLinkTypesMock();

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage(
            'The "bad type" link type wasn\'t found. Verify the type and try again.'
        );

        $this->assertTrue($this->model->setProductLinks('', $links));
    }

    /**
     * Test for setProductLinks() with no product exception
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function testSetProductLinksNoProductException()
    {
        $productSku = self::STUB_PRODUCT_SKU_1;
        $linkType = self::STUB_LINK_TYPE;

        $links = $this->getInputRelatedLinkMock(
            $productSku,
            $linkType,
            self::STUB_PRODUCT_SKU_2,
            self::STUB_PRODUCT_TYPE
        );

        $this->getLinkTypesMock();

        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->willThrowException(
                new NoSuchEntityException(
                    __("The product that was requested doesn't exist. Verify the product and try again.")
                )
            );

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage(
            "The product that was requested doesn't exist. Verify the product and try again."
        );

        $this->model->setProductLinks($productSku, $links);
    }

    /**
     * Test setProductLnks() with invliad data exception
     *
     * @return void
     * @throws CouldNotSaveException
     */
    public function testSetProductLinksInvalidDataException(): void
    {
        $productSku = self::STUB_PRODUCT_SKU_1;
        $linkType = self::STUB_LINK_TYPE;

        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->productMock);

        $links = $this->getInputRelatedLinkMock(
            $productSku,
            $linkType,
            self::STUB_PRODUCT_SKU_2,
            self::STUB_PRODUCT_TYPE
        );

        $this->getLinkTypesMock();

        $this->productMock->expects($this->once())
            ->method('getProductLinks')
            ->willReturn([]);

        $this->productRepositoryMock->expects($this->once())
            ->method('save')
            ->willThrowException(
                new CouldNotSaveException(
                    __("The linked products data is invalid. Verify the data and try again.")
                )
            );

        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage(
            "The linked products data is invalid. Verify the data and try again."
        );

        $this->model->setProductLinks($productSku, $links);
    }

    /**
     * Mock for getLinkTypesMock
     *
     * @return void
     */
    private function getLinkTypesMock(): void
    {
        $linkTypes = [
            'related' => 1,
            'upsell' => 4,
            'crosssell' => 5,
            'associated' => 3
        ];

        $this->linkTypeProviderMock->expects($this->once())
            ->method('getLinkTypes')
            ->willReturn($linkTypes);
    }

    /**
     * get inputRelatedLinkMock
     *
     * @param string $productSku1
     * @param string $linkType
     * @param string $productSku2
     * @param string $typeId
     * @return array
     */
    private function getInputRelatedLinkMock(
        string $productSku1,
        string $linkType,
        string $productSku2,
        string $typeId
    ) {
        $inputRelatedLinkMock = $this->objectManager->getObject(Link::class);
        $inputRelatedLinkMock->setProductSku($productSku1);
        $inputRelatedLinkMock->setLinkType($linkType);
        $inputRelatedLinkMock->setData("sku", $productSku2);
        $inputRelatedLinkMock->setData("type_id", $typeId);
        $inputRelatedLinkMock->setPosition(0);

        return [$inputRelatedLinkMock];
    }
}
