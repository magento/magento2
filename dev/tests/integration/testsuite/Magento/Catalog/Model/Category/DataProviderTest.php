<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Category;

use Magento\Catalog\Model\Category\DataProvider;
use Magento\Eav\Model\Config as EavConfig;
use Magento\TestFramework\Helper\Bootstrap;

class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @var \Magento\Eav\Model\Entity\Type
     */
    private $entityType;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->dataProvider = $objectManager->create(
            DataProvider::class,
            [
                'name' => 'category_form_data_source',
                'primaryFieldName' => 'entity_id',
                'requestFieldName' => 'id'
            ]
        );

        $this->entityType = $objectManager->create(EavConfig::class)->getEntityType('catalog_category');
    }

    /**
     * @return void
     */
    public function testGetMetaRequiredAttributes()
    {
        $requiredAttributes = [
            'general' => ['name'],
            'display_settings' => ['available_sort_by', 'default_sort_by'],
        ];
        $meta = $this->dataProvider->getMeta();
        $this->assertArrayHasKey('url_key', $meta['search_engine_optimization']['children']);
        foreach ($requiredAttributes as $scope => $attributes) {
            foreach ($attributes as $attribute) {
                $this->assertArrayHasKey($attribute, $meta[$scope]['children']);
                $data = $meta[$scope]['children'][$attribute];
                $this->assertTrue($data['arguments']['data']['config']['validation']['required-entry']);
            }
        }
    }
}
