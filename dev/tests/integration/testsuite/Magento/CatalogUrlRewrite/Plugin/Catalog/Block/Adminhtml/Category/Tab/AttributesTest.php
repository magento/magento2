<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Plugin\Catalog\Block\Adminhtml\Category\Tab;

use Magento\Catalog\Model\Category\DataProvider;
use Magento\Eav\Model\Config as EavConfig;
use Magento\TestFramework\Helper\Bootstrap;

class AttributesTest extends \PHPUnit_Framework_TestCase
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
     * test \Magento\CatalogUrlRewrite\Plugin\Catalog\Block\Adminhtml\Category\Tab\Attributes::afterGetAttributesMeta
     * @return void
     */
    public function testGetAttributesMeta()
    {
        $meta = $this->dataProvider->getAttributesMeta($this->entityType);
        $this->assertArrayHasKey('url_key', $meta);
        $this->assertEquals('text', $meta['url_key']['dataType']);
        $this->assertEquals('input', $meta['url_key']['formElement']);
        $this->assertEquals('1', $meta['url_key']['visible']);
        $this->assertEquals('0', $meta['url_key']['required']);
        $this->assertEquals('[STORE VIEW]', $meta['url_key']['scope_label']);
    }
}
