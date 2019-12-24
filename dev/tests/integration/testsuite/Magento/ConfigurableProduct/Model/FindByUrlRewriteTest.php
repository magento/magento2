<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use PHPUnit\Framework\TestCase;

/**
 * Test cases related to check that URL rewrite has created or not.
 */
class FindByUrlRewriteTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManger;

    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManger = Bootstrap::getObjectManager();
        $this->urlFinder = $this->objectManger->get(UrlFinderInterface::class);
        $this->productRepository = $this->objectManger->get(ProductRepositoryInterface::class);
        parent::setUp();
    }

    /**
     * Assert that URL rewrite for child product of configurable was not created.
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_two_child_products.php
     * @magentoDbIsolation enabled
     *
     * @return void
     */
    public function testCheckUrlRewriteForChildWasNotCreated(): void
    {
        $this->checkConfigurableUrlRewriteWasCreated();
        $this->assertNull($this->urlFinder->findOneByData([UrlRewrite::REQUEST_PATH => 'configurable-option-1.html']));
        $this->assertNull($this->urlFinder->findOneByData([UrlRewrite::REQUEST_PATH => 'configurable-option-2.html']));
    }

    /**
     * Assert that URL rewrite for one of child product of configurable was created.
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_product_with_two_child_products.php
     * @magentoDbIsolation enabled
     *
     * @return void
     */
    public function testCheckUrlRewriteForOneOfChildWasCreated(): void
    {
        $this->checkConfigurableUrlRewriteWasCreated();
        $childProduct = $this->productRepository->get('Simple option 1');
        $childProduct->setVisibility(Visibility::VISIBILITY_BOTH);
        $this->productRepository->save($childProduct);
        $childUrlRewrite = $this->urlFinder->findOneByData([UrlRewrite::REQUEST_PATH => 'configurable-option-1.html']);
        $this->assertNotNull($childUrlRewrite);
        $this->assertEquals($childUrlRewrite->getTargetPath(), "catalog/product/view/id/{$childProduct->getId()}");
        $this->assertNull($this->urlFinder->findOneByData([UrlRewrite::REQUEST_PATH => 'configurable-option-2.html']));
    }

    /**
     * Check that configurable url rewrite was created.
     *
     * @return void
     */
    private function checkConfigurableUrlRewriteWasCreated(): void
    {
        $configurableProduct = $this->productRepository->get('Configurable product');
        $configurableUrlRewrite = $this->urlFinder->findOneByData(
            [
                UrlRewrite::REQUEST_PATH => 'configurable-product-with-two-child.html'
            ]
        );
        $this->assertNotNull($configurableUrlRewrite);
        $this->assertEquals(
            $configurableUrlRewrite->getTargetPath(),
            "catalog/product/view/id/{$configurableProduct->getId()}"
        );
    }
}
