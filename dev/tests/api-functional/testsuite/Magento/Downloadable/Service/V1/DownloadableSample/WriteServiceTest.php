<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Service\V1\DownloadableSample;

use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\Sample;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config as RestConfig;

class WriteServiceTest extends WebapiAbstract
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
                'httpMethod' => RestConfig::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => 'downloadableDownloadableSampleWriteServiceV1',
                'serviceVersion' => 'V1',
                'operation' => 'downloadableDownloadableSampleWriteServiceV1Create',
            ],
        ];

        $this->updateServiceInfo = [
            'rest' => [
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => 'downloadableDownloadableSampleWriteServiceV1',
                'serviceVersion' => 'V1',
                'operation' => 'downloadableDownloadableSampleWriteServiceV1Update',
            ],
        ];

        $this->deleteServiceInfo = [
            'rest' => [
                'httpMethod' => RestConfig::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => 'downloadableDownloadableSampleWriteServiceV1',
                'serviceVersion' => 'V1',
                'operation' => 'downloadableDownloadableSampleWriteServiceV1Delete',
            ],
        ];

        $this->testImagePath = __DIR__
            . str_replace('/', DIRECTORY_SEPARATOR, '/../DownloadableLink/_files/test_image.jpg');
    }

    /**
     * Retrieve product that was updated by test
     *
     * @param bool $isScopeGlobal if true product store ID will be set to 0
     * @return Product
     */
    protected function getTargetProduct($isScopeGlobal = false)
    {
        $product = Bootstrap::getObjectManager()->get('Magento\Catalog\Model\ProductFactory')->create()->load(1);
        if ($isScopeGlobal) {
            $product->setStoreId(0);
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
        /** @var $samples \Magento\Downloadable\Model\Resource\Sample\Collection */
        $samples = $product->getTypeInstance()->getSamples($product);
        if (!is_null($sampleId)) {
            /* @var $sample \Magento\Downloadable\Model\Sample  */
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
     *  @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testCreateUploadsProvidedFileContent()
    {
        $requestData = [
            'isGlobalScopeContent' => true,
            'productSku' => 'downloadable-product',
            'sampleContent' => [
                'title' => 'Title',
                'sort_order' => 1,
                'sample_file' => [
                    'data' => base64_encode(file_get_contents($this->testImagePath)),
                    'name' => 'image.jpg',
                ],
                'sample_type' => 'file',
            ],
        ];

        $newSampleId = $this->_webApiCall($this->createServiceInfo, $requestData);
        $globalScopeSample = $this->getTargetSample($this->getTargetProduct(true), $newSampleId);
        $sample = $this->getTargetSample($this->getTargetProduct(), $newSampleId);
        $this->assertNotNull($sample);
        $this->assertEquals($requestData['sampleContent']['title'], $sample->getTitle());
        $this->assertEquals($requestData['sampleContent']['title'], $globalScopeSample->getTitle());
        $this->assertEquals($requestData['sampleContent']['sort_order'], $sample->getSortOrder());
        $this->assertEquals($requestData['sampleContent']['sample_type'], $sample->getSampleType());
        $this->assertStringEndsWith('.jpg', $sample->getSampleFile());
        $this->assertNull($sample->getSampleUrl());
    }

    /**
     *  @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testCreateSavesTitleInStoreViewScope()
    {
        $requestData = [
            'isGlobalScopeContent' => false,
            'productSku' => 'downloadable-product',
            'sampleContent' => [
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
        $this->assertEquals($requestData['sampleContent']['title'], $sample->getTitle());
        $this->assertEquals($requestData['sampleContent']['sort_order'], $sample->getSortOrder());
        $this->assertEquals($requestData['sampleContent']['sample_url'], $sample->getSampleUrl());
        $this->assertEquals($requestData['sampleContent']['sample_type'], $sample->getSampleType());
        $this->assertEmpty($globalScopeSample->getTitle());
    }

    /**
     *  @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testCreateSavesProvidedUrls()
    {
        $requestData = [
            'isGlobalScopeContent' => false,
            'productSku' => 'downloadable-product',
            'sampleContent' => [
                'title' => 'Sample with URL resource',
                'sort_order' => 1,
                'sample_url' => 'http://www.sample.example.com/',
                'sample_type' => 'url',
            ],
        ];

        $newSampleId = $this->_webApiCall($this->createServiceInfo, $requestData);
        $sample = $this->getTargetSample($this->getTargetProduct(), $newSampleId);
        $this->assertNotNull($sample);
        $this->assertEquals($requestData['sampleContent']['title'], $sample->getTitle());
        $this->assertEquals($requestData['sampleContent']['sort_order'], $sample->getSortOrder());
        $this->assertEquals($requestData['sampleContent']['sample_type'], $sample->getSampleType());
        $this->assertEquals($requestData['sampleContent']['sample_url'], $sample->getSampleUrl());
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid sample type.
     */
    public function testCreateThrowsExceptionIfSampleTypeIsNotSpecified()
    {
        $requestData = [
            'isGlobalScopeContent' => false,
            'productSku' => 'downloadable-product',
            'sampleContent' => [
                'title' => 'Sample with URL resource',
                'sort_order' => 1,
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
            'productSku' => 'downloadable-product',
            'sampleContent' => [
                'title' => 'Sample Title',
                'sort_order' => 1,
                'sample_type' => 'file',
                'sample_file' => [
                    'data' => 'not_a_base64_encoded_content',
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
            'productSku' => 'downloadable-product',
            'sampleContent' => [
                'title' => 'Title',
                'sort_order' => 15,
                'sample_type' => 'file',
                'sample_file' => [
                    'data' => base64_encode(file_get_contents($this->testImagePath)),
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
            'productSku' => 'downloadable-product',
            'sampleContent' => [
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
            'productSku' => 'downloadable-product',
            'sampleContent' => [
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
        $this->createServiceInfo['rest']['resourcePath']
            = '/V1/products/simple/downloadable-links/samples';
        $requestData = [
            'isGlobalScopeContent' => false,
            'productSku' => 'simple',
            'sampleContent' => [
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
        $this->createServiceInfo['rest']['resourcePath']
            = '/V1/products/wrong-sku/downloadable-links/samples';
        $requestData = [
            'isGlobalScopeContent' => false,
            'productSku' => 'wrong-sku',
            'sampleContent' => [
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
            'sampleId' => $sampleId,
            'productSku' => 'downloadable-product',
            'sampleContent' => [
                'title' => 'Updated Title',
                'sort_order' => 2,
            ],
        ];

        $this->assertTrue($this->_webApiCall($this->updateServiceInfo, $requestData));
        $sample = $this->getTargetSample($this->getTargetProduct(), $sampleId);
        $this->assertNotNull($sample);
        $this->assertEquals($requestData['sampleContent']['title'], $sample->getTitle());
        $this->assertEquals($requestData['sampleContent']['sort_order'], $sample->getSortOrder());
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
            'sampleId' => $sampleId,
            'productSku' => 'downloadable-product',
            'sampleContent' => [
                'title' => 'Updated Title',
                'sort_order' => 2,
            ],
        ];

        $this->assertTrue($this->_webApiCall($this->updateServiceInfo, $requestData));
        $sample = $this->getTargetSample($this->getTargetProduct(), $sampleId);
        $globalScopeSample = $this->getTargetSample($this->getTargetProduct(true), $sampleId);
        $this->assertNotNull($sample);
        // Title was set on store view level in fixture so it must be the same
        $this->assertEquals($originalSample->getTitle(), $sample->getTitle());
        $this->assertEquals($requestData['sampleContent']['title'], $globalScopeSample->getTitle());
        $this->assertEquals($requestData['sampleContent']['sort_order'], $sample->getSortOrder());
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
            'sampleId' => 1,
            'productSku' => 'wrong-sku',
            'sampleContent' => [
                'title' => 'Updated Title',
                'sort_order' => 2,
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
            'sampleId' => 9999,
            'productSku' => 'downloadable-product',
            'sampleContent' => [
                'title' => 'Title',
                'sort_order' => 2,
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
            'sampleId' => $sampleId,
            'productSku' => 'downloadable-product',
            'sampleContent' => [
                'title' => 'Updated Sample Title',
                'sort_order' => $sortOrder,
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
            'sampleId' => $sampleId,
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
        $this->deleteServiceInfo['rest']['resourcePath']
            = "/V1/products/downloadable-links/samples/{$sampleId}";
        $requestData = [
            'sampleId' => $sampleId,
        ];

        $this->_webApiCall($this->deleteServiceInfo, $requestData);
    }
}
