<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class ProductRepositoryTest for testing ProductRepository interface with Downloadable Product
 */
class ProductRepositoryTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products';
    const PRODUCT_SKU = 'sku-test-product-downloadable';

    /**
     * @var string
     */
    protected $testImagePath;

    protected function setUp()
    {
        $this->testImagePath = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'test_image.jpg';
    }

    /**
     * Execute per test cleanup
     */
    public function tearDown()
    {
        $this->deleteProductBySku(self::PRODUCT_SKU);
        parent::tearDown();
    }

    protected function getLinkData()
    {
        return [
            'link1' => [
                'title' => "link1",
                'sort_order'=> 10,
                'is_shareable' => 1,
                'price' => 2.0,
                'number_of_downloads' => 0,
                'link_type' => 'file',
                'link_file_content' => [
                    'name' => 'link1_content.jpg',
                    'file_data' => base64_encode(file_get_contents($this->testImagePath)),
                ],
                'sample_type' => 'file',
                'sample_file_content' => [
                    'name' => 'link1_sample.jpg',
                    'file_data' => base64_encode(file_get_contents($this->testImagePath)),
                ],
            ],
            'link2' => [
                'title' => 'link2',
                'sort_order'=> 20,
                'is_shareable' => 0,
                'price' => 3.0,
                'number_of_downloads' => 100,
                'link_type' => "url",
                'link_url' => 'http://www.example.com/link2.jpg',
                'sample_type' => 'url',
                'sample_url' => 'http://www.example.com/link2.jpg',
            ],
        ];
    }

    protected function getExpectedLinkData()
    {
        return [
            [
                'title' => 'link1',
                'sort_order' => 10,
                'is_shareable' => 1,
                'price' => 2,
                'number_of_downloads' => 0,
                'link_type' => 'file',
                'sample_type' => 'file',
            ],
            [
                'title' => 'link2',
                'sort_order' => 20,
                'is_shareable' => 0,
                'price' => 3,
                'number_of_downloads' => 100,
                'link_type' => 'url',
                'link_url' => 'http://www.example.com/link2.jpg',
                'sample_type' => 'url',
                'sample_url' => 'http://www.example.com/link2.jpg',
            ],
        ];
    }

    protected function getSampleData()
    {
        return [
            'sample1' => [
                'title' => 'sample1',
                'sort_order' => 10,
                'sample_type' => 'url',
                'sample_url' => 'http://www.example.com/sample1.jpg',
            ],
            'sample2' => [
                'title' => 'sample2',
                'sort_order' => 20,
                'sample_type' => 'file',
                'sample_file_content' => [
                    'name' => 'sample2.jpg',
                    'file_data' => base64_encode(file_get_contents($this->testImagePath)),
                ],
            ],
        ];
    }

    protected function getExpectedSampleData()
    {
        return [
            [
                'title' => 'sample1',
                'sort_order' => 10,
                'sample_type' => 'url',
                'sample_url' => 'http://www.example.com/sample1.jpg',
            ],
            [
                'title' => 'sample2',
                'sort_order' => 20,
                'sample_type' => 'file',
            ],
        ];
    }

    protected function createDownloadableProduct()
    {
        $product = [
            "sku" => self::PRODUCT_SKU,
            "name" => self::PRODUCT_SKU,
            "type_id" => \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE,
            "price" => 10,
            'attribute_set_id' => 4,
            "extension_attributes" => [
                "downloadable_product_links" => array_values($this->getLinkData()),
                "downloadable_product_samples" => array_values($this->getSampleData()),
            ],
        ];

        $response =  $this->createProduct($product);
        $this->assertEquals(self::PRODUCT_SKU, $response[ProductInterface::SKU]);
        $this->assertEquals(10, $response['price']);
        $this->assertEquals(
            \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE,
            $response['type_id']
        );
        return $response;
    }

    /**
     * Create a downloadable product with two links and two samples
     */
    public function testCreateDownloadableProduct()
    {
        $response = $this->createDownloadableProduct();
        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_links"])
        );
        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_samples"])
        );
        $resultLinks
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_links"];
        $this->assertEquals(2, count($resultLinks));
        $this->assertTrue(isset($resultLinks[0]['id']));
        $this->assertTrue(isset($resultLinks[0]['link_file']));
        $this->assertTrue(isset($resultLinks[0]['sample_file']));
        unset($resultLinks[0]['id']);
        unset($resultLinks[0]['link_file']);
        unset($resultLinks[0]['sample_file']);
        $this->assertTrue(isset($resultLinks[1]['id']));
        unset($resultLinks[1]['id']);

        $expectedLinkData = $this->getExpectedLinkData();
        $this->assertEquals($expectedLinkData, $resultLinks);

        $resultSamples = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_samples"];
        $this->assertEquals(2, count($resultSamples));
        $this->assertTrue(isset($resultSamples[0]['id']));
        unset($resultSamples[0]['id']);
        $this->assertTrue(isset($resultSamples[1]['id']));
        $this->assertTrue(isset($resultSamples[1]['sample_file']));
        unset($resultSamples[1]['sample_file']);
        unset($resultSamples[1]['id']);

        $expectedSampleData = $this->getExpectedSampleData();
        $this->assertEquals($expectedSampleData, $resultSamples);
    }

    /**
     * Update downloadable product, update a link, add two link, delete a link
     */
    public function testUpdateDownloadableProductLinks()
    {
        $response = $this->createDownloadableProduct();
        $resultLinks
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_links"];
        $link1Id = $resultLinks[0]['id'];
        $link2Id = $resultLinks[1]['id'];

        $linkFile = $resultLinks[0]['link_file'];
        $sampleFile = $resultLinks[0]['sample_file'];
        $updatedLink1Data = [
            'id' => $link1Id,
            'title' => 'link1_updated',
            'sort_order' => 1, //the sort order needs to be smaller than 10
            'is_shareable' => 0,
            'price' => 5.0,
            'number_of_downloads' => 999,
            'link_type' => 'file',
            'sample_type' => 'file'
        ];
        $linkData = $this->getLinkData();

        $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_links"] =
            [$updatedLink1Data, $linkData['link1'], $linkData['link2']];

        $response = $this->saveProduct($response);
        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_links"])
        );
        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_samples"])
        );
        $resultLinks
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_links"];

        $this->assertEquals(3, count($resultLinks));
        $this->assertTrue(isset($resultLinks[0]['id']));
        $this->assertEquals($link1Id, $resultLinks[0]['id']);
        $this->assertTrue(isset($resultLinks[0]['link_file']));
        $this->assertEquals($linkFile, $resultLinks[0]['link_file']);
        $this->assertTrue(isset($resultLinks[0]['sample_file']));
        $this->assertEquals($sampleFile, $resultLinks[0]['sample_file']);
        unset($resultLinks[0]['id']);
        unset($resultLinks[0]['link_file']);
        unset($resultLinks[0]['sample_file']);
        $this->assertTrue(isset($resultLinks[1]['id']));
        $this->assertGreaterThan($link2Id, $resultLinks[1]['id']);
        $this->assertTrue(isset($resultLinks[1]['link_file']));
        $this->assertTrue(isset($resultLinks[1]['sample_file']));
        unset($resultLinks[1]['id']);
        unset($resultLinks[1]['link_file']);
        unset($resultLinks[1]['sample_file']);
        $this->assertTrue(isset($resultLinks[2]['id']));
        $this->assertGreaterThan($link2Id, $resultLinks[2]['id']);
        unset($resultLinks[2]['id']);

        $expectedLinkData[] = [
            'title' => 'link1_updated',
            'sort_order' => 1, //the sort order needs to be smaller than 10
            'is_shareable' => 0,
            'price' => 5.0,
            'number_of_downloads' => 999,
            'link_type' => 'file',
            'sample_type' => 'file',
        ];
        $expectedLinkData = array_merge($expectedLinkData, $this->getExpectedLinkData());
        $this->assertEquals($expectedLinkData, $resultLinks);

        $resultSamples = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_samples"];
        $this->assertEquals(2, count($resultSamples));
    }

    /**
     * Update downloadable product, update two links and change file content
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testUpdateDownloadableProductLinksWithNewFile()
    {
        $response = $this->createDownloadableProduct();
        $resultLinks
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_links"];
        $link1Id = $resultLinks[0]['id'];
        $link2Id = $resultLinks[1]['id'];

        $linkFile = 'link1_content_updated';
        $sampleFile = 'link1_sample_updated';
        $extension = '.jpg';
        $updatedLink1Data = [
            'id' => $link1Id,
            'title' => 'link1_updated',
            'sort_order' => 1, //the sort order needs to be smaller than 10
            'is_shareable' => 0,
            'price' => 5.0,
            'number_of_downloads' => 999,
            'link_type' => 'file',
            'link_file_content' => [
                'name' => $linkFile . $extension,
                'file_data' => base64_encode(file_get_contents($this->testImagePath)),
            ],
            'sample_type' => 'file',
            'sample_file_content' => [
                'name' => $sampleFile . $extension,
                'file_data' => base64_encode(file_get_contents($this->testImagePath)),
            ],
        ];
        $updatedLink2Data = [
            'id' => $link2Id,
            'title' => 'link2_updated',
            'sort_order' => 2,
            'is_shareable' => 0,
            'price' => 6.0,
            'number_of_downloads' => 0,
            'link_type' => 'file',
            'link_file_content' => [
                'name' => 'link2_content.jpg',
                'file_data' => base64_encode(file_get_contents($this->testImagePath)),
            ],
            'sample_type' => 'file',
            'sample_file_content' => [
                'name' => 'link2_sample.jpg',
                'file_data' => base64_encode(file_get_contents($this->testImagePath)),
            ],
        ];

        $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_links"] =
            [$updatedLink1Data, $updatedLink2Data];

        $response = $this->saveProduct($response);
        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_links"])
        );
        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_samples"])
        );
        $resultLinks
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_links"];

        $this->assertEquals(2, count($resultLinks));
        $this->assertTrue(isset($resultLinks[0]['id']));
        $this->assertEquals($link1Id, $resultLinks[0]['id']);
        $this->assertTrue(isset($resultLinks[0]['link_file']));
        $this->assertGreaterThan(0, strpos($resultLinks[0]['link_file'], $linkFile));
        $this->assertStringEndsWith($extension, $resultLinks[0]['link_file']);
        $this->assertTrue(isset($resultLinks[0]['sample_file']));
        $this->assertGreaterThan(0, strpos($resultLinks[0]['sample_file'], $sampleFile));
        $this->assertStringEndsWith($extension, $resultLinks[0]['sample_file']);
        unset($resultLinks[0]['id']);
        unset($resultLinks[0]['link_file']);
        unset($resultLinks[0]['sample_file']);
        $this->assertTrue(isset($resultLinks[1]['id']));
        $this->assertEquals($link2Id, $resultLinks[1]['id']);
        $this->assertTrue(isset($resultLinks[1]['link_file']));
        $this->assertTrue(isset($resultLinks[1]['sample_file']));
        unset($resultLinks[1]['id']);
        unset($resultLinks[1]['link_file']);
        unset($resultLinks[1]['sample_file']);

        $expectedLinkData = [
            [
                'title' => 'link1_updated',
                'sort_order' => 1, //the sort order needs to be smaller than 10
                'is_shareable' => 0,
                'price' => 5.0,
                'number_of_downloads' => 999,
                'link_type' => 'file',
                'sample_type' => 'file',
            ],
            [
                'title' => 'link2_updated',
                'sort_order' => 2,
                'is_shareable' => 0,
                'price' => 6.0,
                'number_of_downloads' => 0,
                'link_type' => 'file',
                'sample_type' => 'file',
                'link_url' => 'http://www.example.com/link2.jpg', //urls are still saved, just not used
                'sample_url' => 'http://www.example.com/link2.jpg',
            ]
        ];
        $this->assertEquals($expectedLinkData, $resultLinks);

        $resultSamples = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_samples"];
        $this->assertEquals(2, count($resultSamples));
    }

    public function testUpdateDownloadableProductSamples()
    {
        $response = $this->createDownloadableProduct();

        $resultSample
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_samples"];
        $sample1Id = $resultSample[0]['id'];
        $sample2Id = $resultSample[1]['id'];

        $updatedSample1Data = [
            'id' => $sample1Id,
            'title' => 'sample1_updated',
            'sort_order' => 1,
            'sample_type' => 'url',
            'sample_url' => 'http://www.example.com/sample1.jpg',
        ];
        $sampleData = $this->getSampleData();

        $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_samples"] =
            [$updatedSample1Data, $sampleData['sample1'], $sampleData['sample2']];

        $response = $this->saveProduct($response);
        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_links"])
        );
        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_samples"])
        );
        $resultLinks
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_links"];

        $this->assertEquals(2, count($resultLinks));

        $resultSamples = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_samples"];
        $this->assertEquals(3, count($resultSamples));
        $this->assertTrue(isset($resultSamples[0]['id']));
        $this->assertEquals($sample1Id, $resultSamples[0]['id']);
        unset($resultSamples[0]['id']);
        $this->assertTrue(isset($resultSamples[1]['id']));
        $this->assertGreaterThan($sample2Id, $resultSamples[1]['id']);
        unset($resultSamples[1]['id']);
        $this->assertTrue(isset($resultSamples[2]['id']));
        $this->assertGreaterThan($sample2Id, $resultSamples[2]['id']);
        $this->assertTrue(isset($resultSamples[2]['sample_file']));
        unset($resultSamples[2]['sample_file']);
        unset($resultSamples[2]['id']);

        $expectedSampleData[] = [
            'title' => 'sample1_updated',
            'sort_order' => 1,
            'sample_type' => 'url',
            'sample_url' => 'http://www.example.com/sample1.jpg',
        ];
        $expectedSampleData = array_merge($expectedSampleData, $this->getExpectedSampleData());
        $this->assertEquals($expectedSampleData, $resultSamples);
    }

    public function testUpdateDownloadableProductSamplesWithNewFile()
    {
        $response = $this->createDownloadableProduct();

        $resultSample
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_samples"];
        $sample1Id = $resultSample[0]['id'];
        $sample2Id = $resultSample[1]['id'];

        //upload a file for sample 1
        $updatedSample1Data = [
            'id' => $sample1Id,
            'title' => 'sample1_updated',
            'sort_order' => 1,
            'sample_type' => 'file',
            'sample_file_content' => [
                'name' => 'sample1.jpg',
                'file_data' => base64_encode(file_get_contents($this->testImagePath)),
            ],
        ];
        //change title for sample 2
        $updatedSamp2e1Data = [
            'id' => $sample2Id,
            'title' => 'sample2_updated',
            'sort_order' => 2,
            'sample_type' => 'file',
        ];

        $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_samples"] =
            [$updatedSample1Data, $updatedSamp2e1Data];

        $response = $this->saveProduct($response);
        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_links"])
        );
        $this->assertTrue(
            isset($response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_samples"])
        );
        $resultLinks
            = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_links"];

        $this->assertEquals(2, count($resultLinks));

        $resultSamples = $response[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]["downloadable_product_samples"];
        $this->assertEquals(2, count($resultSamples));
        $this->assertTrue(isset($resultSamples[0]['id']));
        $this->assertEquals($sample1Id, $resultSamples[0]['id']);
        unset($resultSamples[0]['id']);
        $this->assertTrue(isset($resultSamples[0]['sample_file']));
        $this->assertContains('sample1', $resultSamples[0]['sample_file']);
        $this->assertStringEndsWith('.jpg', $resultSamples[0]['sample_file']);
        unset($resultSamples[0]['sample_file']);
        $this->assertTrue(isset($resultSamples[1]['id']));
        $this->assertEquals($sample2Id, $resultSamples[1]['id']);
        unset($resultSamples[1]['id']);
        $this->assertTrue(isset($resultSamples[1]['sample_file']));
        $this->assertContains('sample2', $resultSamples[1]['sample_file']);
        $this->assertStringEndsWith('.jpg', $resultSamples[1]['sample_file']);
        unset($resultSamples[1]['sample_file']);

        $expectedSampleData = [
            [
                'title' => 'sample1_updated',
                'sort_order' => 1,
                'sample_type' => 'file',
                'sample_url' => 'http://www.example.com/sample1.jpg',
            ],
            [
                'title' => 'sample2_updated',
                'sort_order' => 2,
                'sample_type' => 'file',
            ],
        ];
        $this->assertEquals($expectedSampleData, $resultSamples);
    }

    /**
     * Get product
     *
     * @param string $productSku
     * @return array the product data
     */
    protected function getProduct($productSku)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $productSku,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $response = (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) ?
            $this->_webApiCall($serviceInfo, ['sku' => $productSku]) : $this->_webApiCall($serviceInfo);

        return $response;
    }

    /**
     * Create product
     *
     * @param array $product
     * @return array the created product data
     */
    protected function createProduct($product)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = ['product' => $product];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        return $response;
    }

    /**
     * Delete a product by sku
     *
     * @param $productSku
     * @return bool
     */
    protected function deleteProductBySku($productSku)
    {
        $resourcePath = self::RESOURCE_PATH . '/' . $productSku;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'deleteById',
            ],
        ];
        $requestData = ["sku" => $productSku];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        return $response;
    }

    /**
     * Save product
     *
     * @param array $product
     * @return array the created product data
     */
    protected function saveProduct($product)
    {
        $resourcePath = self::RESOURCE_PATH . '/' . $product['sku'];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = ['product' => $product];
        $response = $this->_webApiCall($serviceInfo, $requestData);
        return $response;
    }
}
