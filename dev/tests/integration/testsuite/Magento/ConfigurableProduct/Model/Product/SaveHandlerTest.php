<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableResource;

/**
 * Tests for \Magento\ConfigurableProduct\Model\Product\SaveHandler.
 *
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
 */
class SaveHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Object under test
     *
     * @var SaveHandler
     */
    private $handler;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ConfigurableResource
     */
    private $resource;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->product = $this->productRepository->get('configurable');
        $this->resource = Bootstrap::getObjectManager()->create(ConfigurableResource::class);
        $this->handler = Bootstrap::getObjectManager()->create(SaveHandler::class);
    }

    /**
     * @return void
     */
    public function testExecuteWithConfigurableProductLinksChanged(): void
    {
        $childrenIds = $this->product->getTypeInstance()->getChildrenIds($this->product->getId());
        $newChildrenIds = [reset($childrenIds[0])];
        $product = $this->productRepository->getById($this->product->getId());
        $extensionAttributes = $product->getExtensionAttributes();
        $extensionAttributes->setConfigurableProductLinks($newChildrenIds);
        $product->setExtensionAttributes($extensionAttributes);
        $this->handler->execute($product);
        $childrenIds = $this->product->getTypeInstance()->getChildrenIds($this->product->getId());
        $savedChildrenIds = [reset($childrenIds[0])];

        self::assertEquals($newChildrenIds, $savedChildrenIds);
    }

    /**
     * @return void
     */
    public function testExecuteWithConfigurableProductLinksNotChanged(): void
    {
        $childrenIds = $this->product->getTypeInstance()->getChildrenIds($this->product->getId())[0];
        $product = $this->productRepository->getById($this->product->getId());
        $extensionAttributes = $product->getExtensionAttributes();
        $extensionAttributes->setConfigurableProductLinks($childrenIds);
        $product->setExtensionAttributes($extensionAttributes);
        $oldProductLinks = $this->getCurrentProductLinks();
        $this->handler->execute($product);
        $newProductLinks = $this->getCurrentProductLinks();

        self::assertEquals($oldProductLinks, $newProductLinks);
    }

    /**
     * @return array
     */
    private function getCurrentProductLinks()
    {
        $select = $this->resource->getConnection()->select()->from(['l' => $this->resource->getMainTable()]);

        return $this->resource->getConnection()->fetchAll($select);
    }
}
