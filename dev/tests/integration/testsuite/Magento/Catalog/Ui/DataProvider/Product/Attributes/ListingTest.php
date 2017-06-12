<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product\Attributes;

class ListingTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Ui\DataProvider\Product\Attributes\Listing */
    private $dataProvider;

    /** @var \Magento\Framework\App\RequestInterface */
    private $request;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\App\RequestInterface $request */
        $this->request = $objectManager->get(\Magento\Framework\App\RequestInterface::class);

        /** Default Attribute Set Id is equal 4 */
        $this->request->setParams(['template_id' => 4]);

        $this->dataProvider = $objectManager->create(
            \Magento\Catalog\Ui\DataProvider\Product\Attributes\Listing::class,
            [
                'name' => 'product_attributes_grid_data_source',
                'primaryFieldName' => 'attribute_id',
                'requestFieldName' => 'id',
                'request' => $this->request
            ]
        );
    }

    public function testGetDataSortedAsc()
    {
        $this->dataProvider->addOrder('attribute_code', 'asc');
        $data = $this->dataProvider->getData();
        $this->assertEquals(2, $data['totalRecords']);
        $this->assertEquals('color', $data['items'][0]['attribute_code']);
        $this->assertEquals('manufacturer', $data['items'][1]['attribute_code']);
    }

    public function testGetDataSortedDesc()
    {
        $this->dataProvider->addOrder('attribute_code', 'desc');
        $data = $this->dataProvider->getData();
        $this->assertEquals(2, $data['totalRecords']);
        $this->assertEquals('manufacturer', $data['items'][0]['attribute_code']);
        $this->assertEquals('color', $data['items'][1]['attribute_code']);
    }
}
