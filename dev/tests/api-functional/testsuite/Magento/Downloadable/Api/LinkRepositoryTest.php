<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Api;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\Link;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class LinkRepositoryTest extends WebapiAbstract
{
    /**
     * @var array
     */
    protected $createServiceInfo;

    /**
     * @var array
     */
    protected $updateServiceInfo;

    /**
     * @var array
     */
    protected $deleteServiceInfo;

    /**
     * @var string
     */
    protected $testImagePath;

    protected function setUp()
    {
        $this->createServiceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/downloadable-product/downloadable-links',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => 'downloadableLinkRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'downloadableLinkRepositoryV1Save',
            ],
        ];

        $this->updateServiceInfo = [
            'rest' => [
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => 'downloadableLinkRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'downloadableLinkRepositoryV1Save',
            ],
        ];

        $this->deleteServiceInfo = [
            'rest' => [
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => 'downloadableLinkRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'downloadableLinkRepositoryV1Delete',
            ],
        ];

        $this->testImagePath = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'test_image.jpg';
    }

    /**
     * Retrieve product that was updated by test
     *
     * @param bool $isScopeGlobal if true product store ID will be set to 0
     * @return Product
     */
    protected function getTargetProduct($isScopeGlobal = false)
    {
        $objectManager = Bootstrap::getObjectManager();
        if ($isScopeGlobal) {
            $product = $objectManager->get(\Magento\Catalog\Model\ProductFactory::class)
                ->create()
                ->setStoreId(0)
                ->load(1);
        } else {
            $product = $objectManager->get(\Magento\Catalog\Model\ProductFactory::class)->create()->load(1);
        }

        return $product;
    }

    /**
     * Retrieve product link by its ID (or first link if ID is not specified)
     *
     * @param Product $product
     * @param int|null $linkId
     * @return Link|null
     */
    protected function getTargetLink(Product $product, $linkId = null)
    {
        $links = $product->getExtensionAttributes()->getDownloadableProductLinks();
        if ($linkId !== null) {
            if (!empty($links)) {
                foreach ($links as $link) {
                    if ($link->getId() == $linkId) {
                        return $link;
                    }
                }
            }
            return null;
        }

        // return first link
        return reset($links);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testCreateUploadsProvidedFileContent()
    {
        $requestData = [
            'isGlobalScopeContent' => true,
            'sku' => 'downloadable-product',
            'link' => [
                'title' => 'Title',
                'sort_order' => 1,
                'price' => 10.1,
                'is_shareable' => 1,
                'number_of_downloads' => 100,
                'link_type' => 'file',
                'link_file_content' => [
                    'file_data' => base64_encode(file_get_contents($this->testImagePath)),
                    'name' => 'image.jpg',
                ],
                'sample_file_content' => [
                    'file_data' => base64_encode(file_get_contents($this->testImagePath)),
                    'name' => 'image.jpg',
                ],
                'sample_type' => 'file',
            ],
        ];

        $newLinkId = $this->_webApiCall($this->createServiceInfo, $requestData);
        $globalScopeLink = $this->getTargetLink($this->getTargetProduct(true), $newLinkId);
        $link = $this->getTargetLink($this->getTargetProduct(), $newLinkId);
        $this->assertNotNull($link);
        $this->assertEquals($requestData['link']['title'], $link->getTitle());
        $this->assertEquals($requestData['link']['title'], $globalScopeLink->getTitle());
        $this->assertEquals($requestData['link']['sort_order'], $link->getSortOrder());
        $this->assertEquals($requestData['link']['price'], $link->getPrice());
        $this->assertEquals($requestData['link']['price'], $globalScopeLink->getPrice());
        $this->assertEquals($requestData['link']['is_shareable'], (int)$link->getIsShareable());
        $this->assertEquals($requestData['link']['number_of_downloads'], $link->getNumberOfDownloads());
        $this->assertEquals($requestData['link']['link_type'], $link->getLinkType());
        $this->assertEquals($requestData['link']['sample_type'], $link->getSampleType());
        $this->assertStringEndsWith('.jpg', $link->getSampleFile());
        $this->assertStringEndsWith('.jpg', $link->getLinkFile());
        $this->assertNull($link->getLinkUrl());
        $this->assertNull($link->getSampleUrl());
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testCreateSavesPriceAndTitleInStoreViewScope()
    {
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'link' => [
                'title' => 'Store View Title',
                'sort_order' => 1,
                'price' => 150,
                'is_shareable' => 1,
                'number_of_downloads' => 100,
                'link_url' => 'http://www.example.com/',
                'link_type' => 'url',
                'sample_url' => 'http://www.sample.example.com/',
                'sample_type' => 'url',
            ],
        ];

        $newLinkId = $this->_webApiCall($this->createServiceInfo, $requestData);
        $link = $this->getTargetLink($this->getTargetProduct(), $newLinkId);
        $globalScopeLink = $this->getTargetLink($this->getTargetProduct(true), $newLinkId);
        $this->assertNotNull($link);
        $this->assertEquals($requestData['link']['title'], $link->getTitle());
        $this->assertEquals($requestData['link']['sort_order'], $link->getSortOrder());
        $this->assertEquals($requestData['link']['price'], $link->getPrice());
        $this->assertEquals($requestData['link']['is_shareable'], (int)$link->getIsShareable());
        $this->assertEquals($requestData['link']['number_of_downloads'], $link->getNumberOfDownloads());
        $this->assertEquals($requestData['link']['link_url'], $link->getLinkUrl());
        $this->assertEquals($requestData['link']['link_type'], $link->getLinkType());
        $this->assertEquals($requestData['link']['sample_url'], $link->getSampleUrl());
        $this->assertEquals($requestData['link']['sample_type'], $link->getSampleType());
        $this->assertEmpty($globalScopeLink->getTitle());
        $this->assertEmpty($globalScopeLink->getPrice());
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testCreateSavesProvidedUrls()
    {
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'link' => [
                'title' => 'Link with URL resources',
                'sort_order' => 1,
                'price' => 10.1,
                'is_shareable' => 1,
                'number_of_downloads' => 100,
                'link_url' => 'http://www.example.com/',
                'link_type' => 'url',
                'sample_url' => 'http://www.sample.example.com/',
                'sample_type' => 'url',
            ],
        ];

        $newLinkId = $this->_webApiCall($this->createServiceInfo, $requestData);
        $link = $this->getTargetLink($this->getTargetProduct(), $newLinkId);
        $this->assertNotNull($link);
        $this->assertEquals($requestData['link']['title'], $link->getTitle());
        $this->assertEquals($requestData['link']['sort_order'], $link->getSortOrder());
        $this->assertEquals($requestData['link']['price'], $link->getPrice());
        $this->assertEquals($requestData['link']['is_shareable'], (int)$link->getIsShareable());
        $this->assertEquals($requestData['link']['number_of_downloads'], $link->getNumberOfDownloads());
        $this->assertEquals($requestData['link']['link_url'], $link->getLinkUrl());
        $this->assertEquals($requestData['link']['link_type'], $link->getLinkType());
        $this->assertEquals($requestData['link']['sample_type'], $link->getSampleType());
        $this->assertEquals($requestData['link']['sample_url'], $link->getSampleUrl());
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @expectedException \Exception
     * @expectedExceptionMessage The link type is invalid. Verify and try again.
     */
    public function testCreateThrowsExceptionIfLinkTypeIsNotSpecified()
    {
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'link' => [
                'title' => 'Link with URL resources',
                'sort_order' => 1,
                'price' => 10.1,
                'is_shareable' => 1,
                'number_of_downloads' => 100,
                'link_type' => 'invalid',
                'sample_type' => 'url',
                'sample_url' => 'http://www.example.com',
            ],
        ];

        $this->_webApiCall($this->createServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @expectedException \Exception
     * @expectedExceptionMessage Provided content must be valid base64 encoded data.
     */
    public function testCreateThrowsExceptionIfLinkFileContentIsNotAValidBase64EncodedString()
    {
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'link' => [
                'title' => 'Link Title',
                'sort_order' => 1,
                'price' => 10,
                'is_shareable' => 1,
                'number_of_downloads' => 100,
                'link_type' => 'url',
                'link_url' => 'http://www.example.com/',
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
     * @expectedExceptionMessage Provided content must be valid base64 encoded data.
     */
    public function testCreateThrowsExceptionIfSampleFileContentIsNotAValidBase64EncodedString()
    {
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'link' => [
                'title' => 'Link Title',
                'sort_order' => 1,
                'price' => 10,
                'is_shareable' => 1,
                'number_of_downloads' => 100,
                'link_type' => 'file',
                'link_file_content' => [
                    'file_data' => 'not_a_base64_encoded_content',
                    'name' => 'image.jpg',
                ],
                'sample_type' => 'url',
                'sample_url' => 'http://www.example.com/',
            ],
        ];

        $this->_webApiCall($this->createServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @expectedException \Exception
     * @expectedExceptionMessage Provided file name contains forbidden characters.
     */
    public function testCreateThrowsExceptionIfLinkFileNameContainsForbiddenCharacters()
    {
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'link' => [
                'title' => 'Title',
                'sort_order' => 15,
                'price' => 10,
                'is_shareable' => 1,
                'number_of_downloads' => 100,
                'link_type' => 'file',
                'link_file_content' => [
                    'file_data' => base64_encode(file_get_contents($this->testImagePath)),
                    'name' => 'name/with|forbidden{characters',
                ],
                'sample_type' => 'url',
                'sample_url' => 'http://www.example.com/',
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
            'link' => [
                'title' => 'Link Title',
                'sort_order' => 1,
                'price' => 10,
                'is_shareable' => 1,
                'number_of_downloads' => 100,
                'link_type' => 'url',
                'link_url' => 'http://www.example.com/',
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
     * @expectedExceptionMessage Link URL must have valid format.
     */
    public function testCreateThrowsExceptionIfLinkUrlHasWrongFormat()
    {
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'link' => [
                'title' => 'Link Title',
                'sort_order' => 1,
                'price' => 10,
                'is_shareable' => 1,
                'number_of_downloads' => 100,
                'link_type' => 'url',
                'link_url' => 'http://example<.>com/',
                'sample_type' => 'url',
                'sample_url' => 'http://www.example.com/',
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
            'link' => [
                'title' => 'Link Title',
                'sort_order' => 1,
                'price' => 150,
                'is_shareable' => 1,
                'number_of_downloads' => 0,
                'sample_type' => 'url',
                'sample_url' => 'http://example<.>com/',
                'link_type' => 'url',
                'link_url' => 'http://example.com/',
            ],
        ];

        $this->_webApiCall($this->createServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @expectedException \Exception
     * @expectedExceptionMessage Link price must have numeric positive value.
     * @dataProvider getInvalidLinkPrice
     */
    public function testCreateThrowsExceptionIfLinkPriceIsInvalid($linkPrice)
    {
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'link' => [
                'title' => 'Link Title',
                'sort_order' => 1,
                'price' => $linkPrice,
                'is_shareable' => 1,
                'number_of_downloads' => 0,
                'sample_type' => 'url',
                'sample_url' => 'http://example.com/',
                'link_type' => 'url',
                'link_url' => 'http://example.com/',
            ],
        ];

        $this->_webApiCall($this->createServiceInfo, $requestData);
    }

    /**
     * @return array
     */
    public function getInvalidLinkPrice()
    {
        return [
            [-1.5],
        ];
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
            'link' => [
                'title' => 'Link Title',
                'sort_order' => $sortOrder,
                'price' => 10,
                'is_shareable' => 0,
                'number_of_downloads' => 0,
                'sample_type' => 'url',
                'sample_url' => 'http://example.com/',
                'link_type' => 'url',
                'link_url' => 'http://example.com/',
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
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @expectedException \Exception
     * @expectedExceptionMessage Number of downloads must be a positive integer.
     * @dataProvider getInvalidNumberOfDownloads
     */
    public function testCreateThrowsExceptionIfNumberOfDownloadsIsInvalid($numberOfDownloads)
    {
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'link' => [
                'title' => 'Link Title',
                'sort_order' => 0,
                'price' => 10,
                'is_shareable' => 0,
                'number_of_downloads' => $numberOfDownloads,
                'sample_type' => 'url',
                'sample_url' => 'http://example.com/',
                'link_type' => 'url',
                'link_url' => 'http://example.com/',
            ],
        ];
        $this->_webApiCall($this->createServiceInfo, $requestData);
    }

    /**
     * @return array
     */
    public function getInvalidNumberOfDownloads()
    {
        return [
            [-1],
        ];
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @expectedException \Exception
     * @expectedExceptionMessage The product needs to be the downloadable type. Verify the product and try again.
     */
    public function testCreateThrowsExceptionIfTargetProductTypeIsNotDownloadable()
    {
        $this->createServiceInfo['rest']['resourcePath'] = '/V1/products/simple/downloadable-links';
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'simple',
            'link' => [
                'title' => 'Link Title',
                'sort_order' => 50,
                'price' => 200,
                'is_shareable' => 0,
                'number_of_downloads' => 10,
                'sample_type' => 'url',
                'sample_url' => 'http://example.com/',
                'link_type' => 'url',
                'link_url' => 'http://example.com/',
            ],
        ];
        $this->_webApiCall($this->createServiceInfo, $requestData);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The product that was requested doesn't exist. Verify the product and try again.
     */
    public function testCreateThrowsExceptionIfTargetProductDoesNotExist()
    {
        $this->createServiceInfo['rest']['resourcePath'] = '/V1/products/wrong-sku/downloadable-links';
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'wrong-sku',
            'link' => [
                'title' => 'Link Title',
                'sort_order' => 15,
                'price' => 200,
                'is_shareable' => 1,
                'number_of_downloads' => 100,
                'sample_type' => 'url',
                'sample_url' => 'http://example.com/',
                'link_type' => 'url',
                'link_url' => 'http://example.com/',
            ],
        ];
        $this->_webApiCall($this->createServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testUpdate()
    {
        $linkId = $this->getTargetLink($this->getTargetProduct())->getId();
        $this->updateServiceInfo['rest']['resourcePath']
            = "/V1/products/downloadable-product/downloadable-links/{$linkId}";
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'link' => [
                'id' => $linkId,
                'title' => 'Updated Title',
                'sort_order' => 2,
                'price' => 100.10,
                'is_shareable' => 0,
                'number_of_downloads' => 50,
                'link_type' => 'url',
                'sample_type' => 'url',
            ],
        ];
        $this->assertEquals($linkId, $this->_webApiCall($this->updateServiceInfo, $requestData));
        $link = $this->getTargetLink($this->getTargetProduct(), $linkId);
        $this->assertNotNull($link);
        $this->assertEquals($requestData['link']['title'], $link->getTitle());
        $this->assertEquals($requestData['link']['sort_order'], $link->getSortOrder());
        $this->assertEquals($requestData['link']['price'], $link->getPrice());
        $this->assertEquals($requestData['link']['is_shareable'], (int)$link->getIsShareable());
        $this->assertEquals($requestData['link']['number_of_downloads'], $link->getNumberOfDownloads());
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testUpdateSavesDataInGlobalScopeAndDoesNotAffectValuesStoredInStoreViewScope()
    {
        $originalLink = $this->getTargetLink($this->getTargetProduct());
        $linkId = $originalLink->getId();
        $this->updateServiceInfo['rest']['resourcePath']
            = "/V1/products/downloadable-product/downloadable-links/{$linkId}";
        $requestData = [
            'isGlobalScopeContent' => true,
            'sku' => 'downloadable-product',
            'link' => [
                'id' => $linkId,
                'title' => 'Updated Title',
                'sort_order' => 2,
                'price' => 100.10,
                'is_shareable' => 0,
                'number_of_downloads' => 50,
                'link_type' => 'url',
                'sample_type' => 'url',
            ],
        ];

        $this->assertEquals($linkId, $this->_webApiCall($this->updateServiceInfo, $requestData));
        $link = $this->getTargetLink($this->getTargetProduct(), $linkId);
        $globalScopeLink = $this->getTargetLink($this->getTargetProduct(true), $linkId);
        $this->assertNotNull($link);
        // Title and price were set on store view level in fixture so they must be the same
        $this->assertEquals($originalLink->getTitle(), $link->getTitle());
        $this->assertEquals($originalLink->getPrice(), $link->getPrice());
        $this->assertEquals($requestData['link']['title'], $globalScopeLink->getTitle());
        $this->assertEquals($requestData['link']['price'], $globalScopeLink->getPrice());
        $this->assertEquals($requestData['link']['sort_order'], $link->getSortOrder());
        $this->assertEquals($requestData['link']['is_shareable'], (int)$link->getIsShareable());
        $this->assertEquals($requestData['link']['number_of_downloads'], $link->getNumberOfDownloads());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The product that was requested doesn't exist. Verify the product and try again.
     */
    public function testUpdateThrowsExceptionIfTargetProductDoesNotExist()
    {
        $this->updateServiceInfo['rest']['resourcePath'] = '/V1/products/wrong-sku/downloadable-links/1';
        $requestData = [
            'isGlobalScopeContent' => true,
            'sku' => 'wrong-sku',
            'link' => [
                'id' => 1,
                'title' => 'Updated Title',
                'sort_order' => 2,
                'price' => 100.10,
                'is_shareable' => 0,
                'number_of_downloads' => 50,
                'link_type' => 'url',
                'sample_type' => 'url',
            ],
        ];
        $this->_webApiCall($this->updateServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @expectedException \Exception
     * @expectedExceptionMessage No downloadable link with the provided ID was found. Verify the ID and try again.
     */
    public function testUpdateThrowsExceptionIfThereIsNoDownloadableLinkWithGivenId()
    {
        $linkId = 9999;
        $this->updateServiceInfo['rest']['resourcePath']
            = "/V1/products/downloadable-product/downloadable-links/{$linkId}";
        $requestData = [
            'isGlobalScopeContent' => true,
            'sku' => 'downloadable-product',
            'link' => [
                'id' => $linkId,
                'title' => 'Title',
                'sort_order' => 2,
                'price' => 100.10,
                'is_shareable' => 0,
                'number_of_downloads' => 50,
                'link_type' => 'url',
                'sample_type' => 'url',
            ],
        ];

        $this->_webApiCall($this->updateServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @expectedException \Exception
     * @expectedExceptionMessage Link price must have numeric positive value.
     * @dataProvider getInvalidLinkPrice
     */
    public function testUpdateThrowsExceptionIfLinkPriceIsInvalid($linkPrice)
    {
        $linkId = $this->getTargetLink($this->getTargetProduct())->getId();
        $this->updateServiceInfo['rest']['resourcePath']
            = "/V1/products/downloadable-product/downloadable-links/{$linkId}";
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'link' => [
                'id' => $linkId,
                'title' => 'Updated Link Title',
                'sort_order' => 2,
                'price' => $linkPrice,
                'is_shareable' => 0,
                'number_of_downloads' => 50,
                'link_type' => 'url',
                'sample_type' => 'url',
            ],
        ];

        $this->_webApiCall($this->updateServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @expectedException \Exception
     * @expectedExceptionMessage Sort order must be a positive integer.
     * @dataProvider getInvalidSortOrder
     */
    public function testUpdateThrowsExceptionIfSortOrderIsInvalid($sortOrder)
    {
        $linkId = $this->getTargetLink($this->getTargetProduct())->getId();
        $this->updateServiceInfo['rest']['resourcePath']
            = "/V1/products/downloadable-product/downloadable-links/{$linkId}";
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'link' => [
                'id' => $linkId,
                'title' => 'Updated Link Title',
                'sort_order' => $sortOrder,
                'price' => 100.50,
                'is_shareable' => 0,
                'number_of_downloads' => 50,
                'link_type' => 'url',
                'sample_type' => 'url',
            ],
        ];
        $this->_webApiCall($this->updateServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @expectedException \Exception
     * @expectedExceptionMessage Number of downloads must be a positive integer.
     * @dataProvider getInvalidNumberOfDownloads
     */
    public function testUpdateThrowsExceptionIfNumberOfDownloadsIsInvalid($numberOfDownloads)
    {
        $linkId = $this->getTargetLink($this->getTargetProduct())->getId();
        $this->updateServiceInfo['rest']['resourcePath']
            = "/V1/products/downloadable-product/downloadable-links/{$linkId}";
        $requestData = [
            'isGlobalScopeContent' => false,
            'sku' => 'downloadable-product',
            'link' => [
                'id' => $linkId,
                'title' => 'Updated Link Title',
                'sort_order' => 200,
                'price' => 100.50,
                'is_shareable' => 0,
                'number_of_downloads' => $numberOfDownloads,
                'link_type' => 'url',
                'sample_type' => 'url',
            ],
        ];
        $this->_webApiCall($this->updateServiceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Downloadable/_files/product_downloadable.php
     */
    public function testDelete()
    {
        $linkId = $this->getTargetLink($this->getTargetProduct())->getId();
        $this->deleteServiceInfo['rest']['resourcePath'] = "/V1/products/downloadable-links/{$linkId}";
        $requestData = [
            'id' => $linkId,
        ];

        $this->assertTrue($this->_webApiCall($this->deleteServiceInfo, $requestData));
        $link = $this->getTargetLink($this->getTargetProduct(), $linkId);
        $this->assertNull($link);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No downloadable link with the provided ID was found. Verify the ID and try again.
     */
    public function testDeleteThrowsExceptionIfThereIsNoDownloadableLinkWithGivenId()
    {
        $linkId = 9999;
        $this->deleteServiceInfo['rest']['resourcePath'] = "/V1/products/downloadable-links/{$linkId}";
        $requestData = [
            'id' => $linkId,
        ];

        $this->_webApiCall($this->deleteServiceInfo, $requestData);
    }

    /**
     * @dataProvider getListForAbsentProductProvider()
     */
    public function testGetListForAbsentProduct($urlTail, $method)
    {
        $sku = 'absent-product' . time();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . $sku . $urlTail,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'downloadableLinkRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'downloadableLinkRepositoryV1' . $method,
            ],
        ];

        $requestData = ['sku' => $sku];

        $expectedMessage = "The product that was requested doesn't exist. Verify the product and try again.";
        try {
            $this->_webApiCall($serviceInfo, $requestData);
        } catch (\SoapFault $e) {
            $this->assertEquals($expectedMessage, $e->getMessage());
        } catch (\Exception $e) {
            $this->assertContains($expectedMessage, $e->getMessage());
        }
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @dataProvider getListForAbsentProductProvider
     */
    public function testGetListForSimpleProduct($urlTail, $method)
    {
        $sku = 'simple';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . $sku . $urlTail,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'downloadableLinkRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'downloadableLinkRepositoryV1' . $method,
            ],
        ];

        $requestData = ['sku' => $sku];

        $list = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEmpty($list);
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
                'service' => 'downloadableLinkRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'downloadableLinkRepositoryV1' . $method,
            ],
        ];

        $requestData = ['sku' => $sku];

        $list = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertEquals(1, count($list));

        $link = reset($list);
        foreach ($expectations['fields'] as $index => $value) {
            $this->assertEquals($value, $link[$index]);
        }
        $this->assertContains('jellyfish_1_3.jpg', $link['sample_file']);
    }

    public function getListForAbsentProductProvider()
    {
        $linkExpectation = [
            'fields' => [
                'is_shareable' => 2,
                'price' => 15,
                'number_of_downloads' => 15,
                'sample_type' => 'file',
                'link_file' => '/j/e/jellyfish_2_4.jpg',
                'link_type' => 'file'
            ]
        ];

        return [
            'links' => [
                '/downloadable-links',
                'GetList',
                $linkExpectation,
            ],
        ];
    }
}
