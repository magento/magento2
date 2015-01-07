<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Downloadable\Service\V1\DownloadableLink;

use Magento\Webapi\Model\Rest\Config as RestConfig;

class ReadServiceTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    /**
     * @dataProvider getListForAbsentProductProvider()
     */
    public function testGetListForAbsentProduct($urlTail, $method)
    {
        $sku = 'absent-product' . time();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/' . $sku . $urlTail,
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'downloadableDownloadableLinkReadServiceV1',
                'serviceVersion' => 'V1',
                'operation' => 'downloadableDownloadableLinkReadServiceV1' . $method,
            ],
        ];

        $requestData = ['productSku' => $sku];

        $expectedMessage = 'Requested product doesn\'t exist';
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
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'downloadableDownloadableLinkReadServiceV1',
                'serviceVersion' => 'V1',
                'operation' => 'downloadableDownloadableLinkReadServiceV1' . $method,
            ],
        ];

        $requestData = ['productSku' => $sku];

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
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'downloadableDownloadableLinkReadServiceV1',
                'serviceVersion' => 'V1',
                'operation' => 'downloadableDownloadableLinkReadServiceV1' . $method,
            ],
        ];

        $requestData = ['productSku' => $sku];

        $list = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertEquals(1, count($list));

        $link = reset($list);
        foreach ($expectations['fields'] as $index => $value) {
            $this->assertEquals($value, $link[$index]);
        }

        foreach ($expectations['resources'] as $name => $fields) {
            $this->assertNotEmpty($link[$name]);
            $this->assertEquals($fields['file'], $link[$name]['file']);
            $this->assertEquals($fields['type'], $link[$name]['type']);
        }
    }

    public function getListForAbsentProductProvider()
    {
        $sampleIndex = 'sample_resource';
        $linkIndex = 'link_resource';

        $linkExpectation = [
            'fields' => [
                'shareable' => 2,
                'price' => 15,
                'number_of_downloads' => 15,
            ],
            'resources' => [
                $sampleIndex => [
                    'file' => '/n/d/jellyfish_1_3.jpg',
                    'type' => 'file',
                ],
                $linkIndex => [
                    'file' => '/j/e/jellyfish_2_4.jpg',
                    'type' => 'file',
                ],
            ],
        ];

        $sampleExpectation = [
            'fields' => [
                'title' => 'Downloadable Product Sample Title',
                'sort_order' => 0,
            ],
            'resources' => [
                $sampleIndex => [
                    'file' => '/f/u/jellyfish_1_4.jpg',
                    'type' => 'file',
                ],
            ],
        ];

        return [
            'links' => [
                '/downloadable-links',
                'GetLinks',
                $linkExpectation,
            ],
            'samples' => [
                '/downloadable-links/samples',
                'GetSamples',
                $sampleExpectation,
            ],
        ];
    }
}
