<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GroupedProduct\Model\ProductLink;

use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductLink\Data\ListCriteria;
use Magento\Catalog\Model\ProductLink\ProductLinkQuery;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test grouped links.
 */
class GroupedProductLinkQueryTest extends TestCase
{
    /**
     * @var ProductLinkQuery
     */
    private $query;

    /**
     * @var ProductRepository
     */
    private $productRepo;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();
        $this->query = $objectManager->get(ProductLinkQuery::class);
        $this->productRepo = $objectManager->get(ProductRepository::class);
        $this->criteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
    }

    /**
     * Test getting links for a list of products.
     *
     * @magentoDataFixture Magento/GroupedProduct/_files/product_grouped.php
     * @return void
     * @throws \Throwable
     */
    public function testSearch(): void
    {
        $sku = 'grouped-product';
        //Generating criteria
        /** @var ListCriteria[] $criteriaList */
        $criteriaList = [
            new ListCriteria($sku, ['associated']),
            new ListCriteria($sku, ['related'])
        ];

        //Finding the list
        $result = $this->query->search($criteriaList);
        //Validating results
        //1st criteria
        $this->assertEmpty($result[0]->getError());
        $this->assertNotEmpty($result[0]->getResult());
        $this->assertCount(2, $result[0]->getResult());
        foreach ($result[0]->getResult() as $link) {
            $this->assertEquals($sku, $link->getSku());
            $this->assertEquals('associated', $link->getLinkType());
            $this->assertContains($link->getLinkedProductSku(), ['virtual-product', 'simple']);
            $this->assertNotEmpty($link->getExtensionAttributes()->getQty());
        }
        //2nd criteria
        $this->assertEmpty($result[1]->getError());
        $this->assertEmpty($result[1]->getResult());
    }
}
