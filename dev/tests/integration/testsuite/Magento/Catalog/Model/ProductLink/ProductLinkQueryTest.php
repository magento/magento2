<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\ProductLink;

use Magento\Catalog\Model\ProductLink\Data\ListCriteria;
use Magento\Catalog\Model\ProductLink\Data\ListCriteriaInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test links query.
 */
class ProductLinkQueryTest extends TestCase
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
     * Generate search criteria.
     *
     * @param \Magento\Catalog\Model\Product[] $products
     * @return ListCriteriaInterface[]
     */
    private function generateCriteriaList(array $products): array
    {
        $typesList = ['related', 'crosssell', 'upsell'];
        /** @var ListCriteriaInterface[] $criteriaList */
        $criteriaList = [];
        foreach ($products as $product) {
            $sku = $product->getSku();
            $typesFilter = [$typesList[rand(0, 2)], $typesList[rand(0, 2)]];
            //Not always providing product entity or the default criteria implementation for testing purposes.
            //Getting 1 list with types filter and one without.
            $criteriaList[] = new ListCriteria($sku, $typesFilter, $product);
            $criteria = new class implements ListCriteriaInterface
            {
                /**
                 * @var string
                 */
                public $sku;

                /**
                 * @inheritDoc
                 */
                public function getBelongsToProductSku(): string
                {
                    return $this->sku;
                }

                /**
                 * @inheritDoc
                 */
                public function getLinkTypes(): ?array
                {
                    return null;
                }
            };
            $criteria->sku = $sku;
            $criteriaList[] = $criteria;
        }

        return $criteriaList;
    }

    /**
     * Test getting links for a list of products.
     *
     * @magentoDataFixture Magento/Catalog/_files/multiple_related_products.php
     * @return void
     * @throws \Throwable
     */
    public function testSearch(): void
    {
        //Finding root products
        $list = $this->productRepo->getList(
            $this->criteriaBuilder->addFilter('sku', 'simple-related-%', 'like')->create()
        );
        //Creating criteria
        $criteriaList = $this->generateCriteriaList($list->getItems());
        $this->assertNotEmpty($criteriaList);
        //Searching
        $result = $this->query->search($criteriaList);
        //Checking results
        $this->assertCount(count($criteriaList), $result);
        foreach ($criteriaList as $index => $criteria) {
            //No errors, links must be found
            $this->assertNull($result[$index]->getError());
            if (!$criteria->getLinkTypes()) {
                //If there were no types filter the list cannot be empty
                $this->assertNotEmpty($result[$index]->getResult());
            }
            foreach ($result[$index]->getResult() as $link) {
                //Links must belong to requested products.
                $this->assertEquals($criteria->getBelongsToProductSku(), $link->getSku());
                if ($criteria->getLinkTypes()) {
                    //If link filter was set no other link types must be returned
                    $this->assertContains($link->getLinkType(), $criteria->getLinkTypes());
                }
                //Type must be accurate
                $this->assertEquals(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE, $link->getLinkedProductType());
                //Determining whether the product is supposed to be linked by SKU
                preg_match('/^simple\-related\-(\d+)$/i', $criteria->getBelongsToProductSku(), $productIndex);
                $this->assertNotEmpty($productIndex);
                $this->assertFalse(empty($productIndex[1]));
                $productIndex = (int)$productIndex[1];
                $this->assertRegExp('/^related\-product\-' .$productIndex .'\-\d+$/i', $link->getLinkedProductSku());
                //Position must be set
                $this->assertGreaterThan(0, $link->getPosition());
            }
        }
    }
}
