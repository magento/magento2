<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Api;

use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\Sample;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * API tests for Magento\Downloadable\Model\SampleRepository.
 */
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

    /**
     * @var DomainManagerInterface
     */
    private $domainManager;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->domainManager = $objectManager->get(DomainManagerInterface::class);
        $this->domainManager->addDomains(['example.com']);

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
     * Remove example domain from whitelist and call parent restore configuration
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->domainManager->removeDomains(['example.com']);
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
            $product = Bootstrap::getObjectManager()->get(\Magento\Catalog\Model\ProductFactory::class)
                ->create()->setStoreId(0)->load(1);
        } else {
            $product = Bootstrap::getObjectManager()->get(\Magento\Catalog\Model\ProductFactory::class)
                ->create()
                ->load(1);
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
        $samples = $product->getExtensionAttributes()->getDownloadableProductSamples();
        if ($sampleId) {
            /* @var $sample \Magento\Downloadable\Model\Sample */
            if ($samples) {
                foreach ($samples as $sample) {
                    if ($sample->getId() == $sampleId) {
                        return $sample;
                    }
                }
            }

            return null;
        }

        // return first sample
        return $samples[0];
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
                    //phpcs:ignore Magento2.Functions.DiscouragedFunction
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
    public function testCreateThrowsExceptionIfSampleTypeIsInvalid()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The sample type is invalid. Verify the sample type and try again.');

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
     * Check that error appears when sample file not existing in filesystem.
     *
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @return void
     */
    public function testCreateSampleWithMissingFileThrowsException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Sample file not found. Please try again.');

        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'sample' => [
                'title' => 'Link Title',
                'sort_order' => 1,
                'sample_type' => 'file',
                'sample_file' => '/n/o/nexistfile.png',
            ],
        ];

        $this->_webApiCall($this->createServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testCreateThrowsExceptionIfSampleFileContentIsNotAValidBase64EncodedString()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Provided content must be valid base64 encoded data.');

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
     */
    public function testCreateThrowsExceptionIfSampleFileNameContainsForbiddenCharacters()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Provided file name contains forbidden characters.');

        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'sample' => [
                'title' => 'Title',
                'sort_order' => 15,
                'sample_type' => 'file',
                'sample_file_content' => [
                    //phpcs:ignore Magento2.Functions.DiscouragedFunction
                    'file_data' => base64_encode(file_get_contents($this->testImagePath)),
                    'name' => 'name/with|forbidden{characters',
                ],
            ],
        ];

        $this->_webApiCall($this->createServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testCreateThrowsExceptionIfSampleUrlHasWrongFormat()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Sample URL must have valid format.');

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
     * @dataProvider getInvalidSortOrder
     */
    public function testCreateThrowsExceptionIfSortOrderIsInvalid($sortOrder)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Sort order must be a positive integer.');

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
    public static function getInvalidSortOrder()
    {
        return [
            [-1],
        ];
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
                'sample_url' => 'http://google.com',
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
                'sample_url' => 'http://google.com',
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
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     */
    public function testUpdateThrowsExceptionIfThereIsNoDownloadableSampleWithGivenId()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'No downloadable sample with the provided ID was found. Verify the ID and try again.'
        );

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
     * @dataProvider getInvalidSortOrder
     */
    public function testUpdateThrowsExceptionIfSortOrderIsInvalid($sortOrder)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Sort order must be a positive integer.');

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

        $this->assertCount(1, $list);

        $link = reset($list);
        foreach ($expectations['fields'] as $index => $value) {
            $this->assertEquals($value, $link[$index]);
        }
        $this->assertNotEmpty($link['sample_file']);
    }

    public static function getListForAbsentProductProvider()
    {
        $sampleExpectation = [
            'fields' => [
                'title' => 'Downloadable Product Sample Title',
                'sort_order' => 0,
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

    /**
     */
    public function testDeleteThrowsExceptionIfThereIsNoDownloadableSampleWithGivenId()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'No downloadable sample with the provided ID was found. Verify the ID and try again.'
        );

        $sampleId = 9999;
        $this->deleteServiceInfo['rest']['resourcePath'] = "/V1/products/downloadable-links/samples/{$sampleId}";
        $requestData = [
            'id' => $sampleId,
        ];

        $this->_webApiCall($this->deleteServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testCreateThrowsExceptionIfTargetProductTypeIsNotDownloadable()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'The product needs to be the downloadable type. Verify the product and try again.'
        );

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
     */
    public function testCreateThrowsExceptionIfTargetProductDoesNotExist()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'The product that was requested doesn\'t exist. Verify the product and try again.'
        );

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
     */
    public function testUpdateThrowsExceptionIfTargetProductDoesNotExist()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'The product that was requested doesn\'t exist. Verify the product and try again.'
        );

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
}
