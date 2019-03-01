<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute\Source;

use Magento\TestFramework\Helper\CacheCleaner;
use \Magento\Framework\Data\Collection;

class CountryofmanufactureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * code of attribute for test
     */
    const ATTRIBUTE_CODE = 'country_of_manufacture';

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Countryofmanufacture
     */
    private $model;

    /**
     * @var
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(
            \Magento\Catalog\Model\Product\Attribute\Source\Countryofmanufacture::class
        );
    }

    public function testGetAllOptions()
    {
        CacheCleaner::cleanAll();
        $allOptions = $this->model->getAllOptions();
        $cachedAllOptions = $this->model->getAllOptions();
        $this->assertEquals($allOptions, $cachedAllOptions);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_simple_with_country_of_manufacture.php
     */
    public function testAddValueSortToCollection()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
        $attr = $this->objectManager->get(\Magento\Eav\Model\Config::class)
                                                       ->getAttribute('catalog_product', self::ATTRIBUTE_CODE);
        $this->model->setAttribute($attr);
        $this->model->addValueSortToCollection($collection, Collection::SORT_ORDER_ASC);
        $collection->addAttributeToSelect(self::ATTRIBUTE_CODE);
        $countries = [];
        foreach ($collection->getItems() as $item) {
            $countries[] = $item->getData(self::ATTRIBUTE_CODE);
        }
        $this->assertEquals(['CM', 'UA'], $countries);
    }
}
