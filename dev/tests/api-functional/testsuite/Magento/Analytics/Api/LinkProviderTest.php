<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Api;

use Magento\Framework\UrlInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class LinkProviderTest.
 *
 * Checks that api for providing link to encrypted archive works.
 */
class LinkProviderTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'analyticsLinkProviderV1';
    const RESOURCE_PATH = '/V1/analytics/link';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Analytics/_files/create_link.php
     */
    public function testGetAll()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /**
         * @var $fileInfoManager \Magento\Analytics\Model\FileInfoManager
         */
        $fileInfoManager = $objectManager->create(\Magento\Analytics\Model\FileInfoManager::class);

        $storeManager = $objectManager->create(\Magento\Store\Model\StoreManagerInterface::class);

        $fileInfo = $fileInfoManager->load();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        $response = $this->_webApiCall($serviceInfo);
        $this->assertEquals(2, count($response));
        $this->assertEquals(base64_encode($fileInfo->getInitializationVector()), $response['initialization_vector']);
        $this->assertEquals(
            $storeManager->getStore()->getBaseUrl(
                UrlInterface::URL_TYPE_MEDIA
            ) . $fileInfo->getPath(),
            $response['url']
        );
    }
}
