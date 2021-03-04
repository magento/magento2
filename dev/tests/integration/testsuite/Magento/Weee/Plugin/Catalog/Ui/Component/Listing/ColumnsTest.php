<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Plugin\Catalog\Ui\Component\Listing;

use Magento\Catalog\Ui\Component\Listing\Attribute\Repository;
use Magento\Catalog\Ui\Component\Listing\Columns;
use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class ColumnsTest
 * Check if FPT attribute column in product grid won't be sortable
 */
class ColumnsTest extends TestCase
{
    /**
     * @var Columns
     */
    private $columns;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $attributeRepository = $objectManager->get(Repository::class);
        $dataProvider = $objectManager->create(
            ProductDataProvider::class,
            [
                'name' => "product_listing_data_source",
                'primaryFieldName' => "entity_id",
                'requestFieldName' => "id",
            ]
        );
        $context = $objectManager->create(ContextInterface::class);
        $context->setDataProvider($dataProvider);
        $this->columns = $objectManager->create(
            Columns::class,
            ['attributeRepository' => $attributeRepository, 'context' => $context]
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Weee/_files/fixed_product_attribute.php
     */
    public function testGetProductWeeeAttributesConfig()
    {
        $this->columns->prepare();
        $column = $this->columns->getComponent('fixed_product_attribute');
        $columnConfig = $column->getData('config');
        $this->assertArrayHasKey('sortable', $columnConfig);
        $this->assertFalse($columnConfig['sortable']);
    }
}
