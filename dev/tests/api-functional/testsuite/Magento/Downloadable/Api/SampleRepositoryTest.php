<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api;

use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\Sample;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class SampleRepositoryTest extends WebapiAbstract
{
    /**
     * @var array
     */
    protected $createServiceInfo;

    /**
     * @var string
     */
    protected $testImagePath;

    /**
     * @var array
     */
    protected $updateServiceInfo;

    /**
     * @var array
     */
    protected $deleteServiceInfo;

    protected function setUp()
    {
        $this->createServiceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/downloadable-product/downloadable-links/samples',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => 'downloadableSampleRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'downloadableSampleRepositoryV1Save',
            ],
        ];

        $this->updateServiceInfo = [
            'rest' => [
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => 'downloadableSampleRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'downloadableSampleRepositoryV1Save',
            ],
        ];

        $this->deleteServiceInfo = [
            'rest' => [
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => 'downloadableSampleRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'downloadableSampleRepositoryV1Delete',
            ],
        ];

        $this->testImagePath = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/_files/test_image.jpg');
    }

    /**
     * Retrieve product that was updated by test
     *
     * @param bool $isScopeGlobal if true product store ID will be set to 0
     * @return Product
     */
    protected function getTargetProduct($isScopeGlobal = false)
    {
        if ($isScopeGlobal) {
            $product = Bootstrap::getObjectManager()->get('Magento\Catalog\Model\ProductFactory')
                ->create()->setStoreId(0)->load(1);
        } else {
            $product = Bootstrap::getObjectManager()->get('Magento\Catalog\Model\ProductFactory')->create()->load(1);
        }

        return $product;
    }

    /**
     * Retrieve product sample by its ID (or first sample if ID is not specified)
     *
     * @param Product $product
     * @param int|null $sampleId
     * @return Sample|null
     */
    protected function getTargetSample(Product $product, $sampleId = null)
    {
        /** @var $samples \Magento\Downloadable\Model\ResourceModel\Sample\Collection */
        $samples = $product->getTypeInstance()->getSamples($product);
        if ($sampleId !== null) {
            /* @var $sample \Magento\Downloadable\Model\Sample */
            foreach ($samples as $sample) {
                if ($sample->getId() == $sampleId) {
                    return $sample;
                }
            }

            return null;
        }

        // return first sample
        return $samples->getFirstItem();
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testCreateUploadsProvidedFileContent()
    {
        $requestData = [
            'isGlobalScopeContent' => true,
            'sku' => 'downloadable-product',
            'sample' => [
                'title' => 'Title',
                'sort_order' => 1,
                'sample_file_content' => [
                    'file_data' => base64_encode(file_get_contents($this->testImagePath)),
                    'name' => 'image.jpg',
                ],
                'sample_type' => 'file',
            ],
        ];

        $newSampleId = $this->_webApiCall($this->createServiceInfo, $requestData);
        $globalScopeSample = $this->getTargetSample($this->getTargetProduct(true), $newSampleId);
        $sample = $this->getTargetSample($this->getTargetProduct(), $newSampleId);
        $this->assertNotNull($sample);
        $this->assertNotNull($sample->getId());
        $this->assertEquals($requestData['sample']['title'], $sample->getTitle());
        $this->assertEquals($requestData['sample']['title'], $globalScopeSample->getTitle());
        $this->assertEquals($requestData['sample']['sort_order'], $sample->getSortOrder());
        $this->assertEquals($requestData['sample']['sample_type'], $sample->getSampleType());
        $this->assertStringEndsWith('.jpg', $sample->getSampleFile());
        $this->assertNull($sample->getSampleUrl());
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testCreateSavesTitleInStoreViewScope()
    {
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'sample' => [
                'title' => 'Store View Title',
                'sort_order' => 1,
                'sample_url' => 'http://www.sample.example.com/',
                'sample_type' => 'url',
            ],
        ];

        $newSampleId = $this->_webApiCall($this->createServiceInfo, $requestData);
        $sample = $this->getTargetSample($this->getTargetProduct(), $newSampleId);
        $globalScopeSample = $this->getTargetSample($this->getTargetProduct(true), $newSampleId);
        $this->assertNotNull($sample);
        $this->assertEquals($requestData['sample']['title'], $sample->getTitle());
        $this->assertEquals($requestData['sample']['sort_order'], $sample->getSortOrder());
        $this->assertEquals($requestData['sample']['sample_url'], $sample->getSampleUrl());
        $this->assertEquals($requestData['sample']['sample_type'], $sample->getSampleType());
        $this->assertEmpty($globalScopeSample->getTitle());
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testCreateSavesProvidedUrls()
    {
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'sample' => [
                'title' => 'Sample with URL resource',
                'sort_order' => 1,
                'sample_url' => 'http://www.sample.example.com/',
                'sample_type' => 'url',
            ],
        ];

        $newSampleId = $this->_webApiCall($this->createServiceInfo, $requestData);
        $sample = $this->getTargetSample($this->getTargetProduct(), $newSampleId);
        $this->assertNotNull($sample);
        $this->assertEquals($requestData['sample']['title'], $sample->getTitle());
        $this->assertEquals($requestData['sample']['sort_order'], $sample->getSortOrder());
        $this->assertEquals($requestData['sample']['sample_type'], $sample->getSampleType());
        $this->assertEquals($requestData['sample']['sample_url'], $sample->getSampleUrl());
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid sample type.
     */
    public function testCreateThrowsExceptionIfSampleTypeIsInvalid()
    {
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'sample' => [
                'title' => 'Sample with URL resource',
                'sort_order' => 1,
                'sample_type' => 'invalid',
            ],
        ];

        $this->_webApiCall($this->createServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @expectedException \Exception
     * @expectedExceptionMessage Provided content must be valid base64 encoded data.
     */
    public function testCreateThrowsExceptionIfSampleFileContentIsNotAValidBase64EncodedString()
    {
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'sample' => [
                'title' => 'Sample Title',
                'sort_order' => 1,
                'sample_type' => 'file',
                'sample_file_content' => [
                    'file_data' => 'not_a_base64_encoded_content',
                    'name' => 'image.jpg',
                ],
            ],
        ];

        $this->_webApiCall($this->createServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @expectedException \Exception
     * @expectedExceptionMessage Provided file name contains forbidden characters.
     */
    public function testCreateThrowsExceptionIfSampleFileNameContainsForbiddenCharacters()
    {
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'sample' => [
                'title' => 'Title',
                'sort_order' => 15,
                'sample_type' => 'file',
                'sample_file_content' => [
                    'file_data' => base64_encode(file_get_contents($this->testImagePath)),
                    'name' => 'name/with|forbidden{characters',
                ],
            ],
        ];

        $this->_webApiCall($this->createServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @expectedException \Exception
     * @expectedExceptionMessage Sample URL must have valid format.
     */
    public function testCreateThrowsExceptionIfSampleUrlHasWrongFormat()
    {
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'sample' => [
                'title' => 'Sample Title',
                'sort_order' => 1,
                'sample_type' => 'url',
                'sample_url' => 'http://example<.>com/',
            ],
        ];

        $this->_webApiCall($this->createServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @expectedException \Exception
     * @expectedExceptionMessage Sort order must be a positive integer.
     * @dataProvider getInvalidSortOrder
     */
    public function testCreateThrowsExceptionIfSortOrderIsInvalid($sortOrder)
    {
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'sample' => [
                'title' => 'Sample Title',
                'sort_order' => $sortOrder,
                'sample_type' => 'url',
                'sample_url' => 'http://example.com/',
            ],
        ];
        $this->_webApiCall($this->createServiceInfo, $requestData);
    }

    /**
     * @return array
     */
    public function getInvalidSortOrder()
    {
        return [
            [-1],
        ];
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @expectedException \Exception
     * @expectedExceptionMessage Product type of the product must be 'downloadable'.
     */
    public function testCreateThrowsExceptionIfTargetProductTypeIsNotDownloadable()
    {
        $this->createServiceInfo['rest']['resourcePath'] = '/V1/products/simple/downloadable-links/samples';
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'simple',
            'sample' => [
                'title' => 'Sample Title',
                'sort_order' => 50,
                'sample_type' => 'url',
                'sample_url' => 'http://example.com/',
            ],
        ];
        $this->_webApiCall($this->createServiceInfo, $requestData);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Requested product doesn't exist
     */
    public function testCreateThrowsExceptionIfTargetProductDoesNotExist()
    {
        $this->createServiceInfo['rest']['resourcePath'] = '/V1/products/wrong-sku/downloadable-links/samples';
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'wrong-sku',
            'sample' => [
                'title' => 'Title',
                'sort_order' => 15,
                'sample_type' => 'url',
                'sample_url' => 'http://example.com/',
            ],
        ];
        $this->_webApiCall($this->createServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     */
    public function testUpdate()
    {
        $sampleId = $this->getTargetSample($this->getTargetProduct())->getId();
        $this->updateServiceInfo['rest']['resourcePath']
            = "/V1/products/downloadable-product/downloadable-links/samples/{$sampleId}";
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'sample' => [
                'id' => $sampleId,
                'title' => 'Updated Title',
                'sort_order' => 2,
                'sample_type' => 'url',
            ],
        ];

        $this->assertEquals($sampleId, $this->_webApiCall($this->updateServiceInfo, $requestData));
        $sample = $this->getTargetSample($this->getTargetProduct(), $sampleId);
        $this->assertNotNull($sample);
        $this->assertEquals($requestData['sample']['id'], $sample->getId());
        $this->assertEquals($requestData['sample']['title'], $sample->getTitle());
        $this->assertEquals($requestData['sample']['sort_order'], $sample->getSortOrder());
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     */
    public function testUpdateSavesDataInGlobalScopeAndDoesNotAffectValuesStoredInStoreViewScope()
    {
        $originalSample = $this->getTargetSample($this->getTargetProduct());
        $sampleId = $originalSample->getId();
        $this->updateServiceInfo['rest']['resourcePath']
            = "/V1/products/downloadable-product/downloadable-links/samples/{$sampleId}";
        $requestData = [
            'isGlobalScopeContent' => true,
            'sku' => 'downloadable-product',
            'sample' => [
                'id' => $sampleId,
                'title' => 'Updated Title',
                'sort_order' => 2,
                'sample_type' => 'url',
            ],
        ];

        $this->assertEquals($sampleId, $this->_webApiCall($this->updateServiceInfo, $requestData));
        $sample = $this->getTargetSample($this->getTargetProduct(), $sampleId);
        $globalScopeSample = $this->getTargetSample($this->getTargetProduct(true), $sampleId);
        $this->assertNotNull($sample);
        // Title was set on store view level in fixture so it must be the same
        $this->assertEquals($originalSample->getTitle(), $sample->getTitle());
        $this->assertEquals($requestData['sample']['title'], $globalScopeSample->getTitle());
        $this->assertEquals($requestData['sample']['sort_order'], $sample->getSortOrder());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Requested product doesn't exist
     */
    public function testUpdateThrowsExceptionIfTargetProductDoesNotExist()
    {
        $this->updateServiceInfo['rest']['resourcePath'] = '/V1/products/wrong-sku/downloadable-links/samples/1';
        $requestData = [
            'isGlobalScopeContent' => true,
            'sku' => 'wrong-sku',
            'sample' => [
                'id' => 1,
                'title' => 'Updated Title',
                'sort_order' => 2,
                'sample_type' => 'url',
            ],
        ];
        $this->_webApiCall($this->updateServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     * @expectedException \Exception
     * @expectedExceptionMessage There is no downloadable sample with provided ID.
     */
    public function testUpdateThrowsExceptionIfThereIsNoDownloadableSampleWithGivenId()
    {
        $sampleId = 9999;
        $this->updateServiceInfo['rest']['resourcePath']
            = "/V1/products/downloadable-product/downloadable-links/samples/{$sampleId}";
        $requestData = [
            'isGlobalScopeContent' => true,
            'sku' => 'downloadable-product',
            'sample' => [
                'id' => $sampleId,
                'title' => 'Title',
                'sort_order' => 2,
                'sample_type' => 'url',
            ],
        ];

        $this->_webApiCall($this->updateServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     * @expectedException \Exception
     * @expectedExceptionMessage Sort order must be a positive integer.
     * @dataProvider getInvalidSortOrder
     */
    public function testUpdateThrowsExceptionIfSortOrderIsInvalid($sortOrder)
    {
        $sampleId = $this->getTargetSample($this->getTargetProduct())->getId();
        $this->updateServiceInfo['rest']['resourcePath']
            = "/V1/products/downloadable-product/downloadable-links/samples/{$sampleId}";
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'sample' => [
                'id' => $sampleId,
                'title' => 'Updated Sample Title',
                'sort_order' => $sortOrder,
                'sample_type' => 'url',
            ],
        ];
        $this->_webApiCall($this->updateServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     */
    public function testDelete()
    {
        $sampleId = $this->getTargetSample($this->getTargetProduct())->getId();
        $this->deleteServiceInfo['rest']['resourcePath'] = "/V1/products/downloadable-links/samples/{$sampleId}";
        $requestData = [
            'id' => $sampleId,
        ];

        $this->assertTrue($this->_webApiCall($this->deleteServiceInfo, $requestData));
        $sample = $this->getTargetSample($this->getTargetProduct(), $sampleId);
        $this->assertNull($sample);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage There is no downloadable sample with provided ID.
     */
    public function testDeleteThrowsExceptionIfThereIsNoDownloadableSampleWithGivenId()
    {
        $sampleId = 9999;
        $this->deleteServiceInfo['rest']['resourcePath'] = "/V1/products/downloadable-links/samples/{$sampleId}";
        $requestData = [
            'id' => $sampleId,
        ];

        $this->_webApiCall($this->deleteServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     * @dataProvider getListForAbsentProductProvider
     */
    public function testGetList($urlTail, $method, $expectations)
    {
        $sku = 'downloadable-product';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . $sku . $urlTail,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'downloadableSampleRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'downloadableSampleRepositoryV1' . $method,
            ],
        ];

        $requestData = ['sku' => $sku];

        $list = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertEquals(1, count($list));

        $link = reset($list);
        foreach ($expectations['fields'] as $index => $value) {
            $this->assertEquals($value, $link[$index]);
        }
    }

    public function getListForAbsentProductProvider()
    {
        $sampleExpectation = [
            'fields' => [
                'title' => 'Downloadable Product Sample Title',
                'sort_order' => 0,
                'sample_file' => '/f/u/jellyfish_1_4.jpg',
                'sample_type' => 'file'
            ]
        ];

        return [
            'samples' => [
                '/downloadable-links/samples',
                'GetList',
                $sampleExpectation,
            ],
        ];
    }
}
