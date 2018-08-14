<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Api;

use Magento\Analytics\Model\FileInfoManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
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

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Analytics/_files/create_link.php
     */
    public function testGetAll()
    {
        $objectManager = Bootstrap::getObjectManager();

        /**
         * @var $fileInfoManager FileInfoManager
         */
        $fileInfoManager = $objectManager->create(FileInfoManager::class);

        $storeManager = $objectManager->create(StoreManagerInterface::class);

        $fileInfo = $fileInfoManager->load();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => static::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => static::SERVICE_NAME,
                'serviceVersion' => static::SERVICE_VERSION,
                'operation' => static::SERVICE_NAME . 'Get',
            ],
        ];
        if (!$this->isTestBaseUrlSecure()) {
            try {
                $this->_webApiCall($serviceInfo);
            } catch (\Exception $e) {
                $this->assertContains(
                    'Operation allowed only in HTTPS',
                    $e->getMessage()
                );
                return;
            }
            $this->fail("Exception 'Operation allowed only in HTTPS' should be thrown");
        } else {
            $response = $this->_webApiCall($serviceInfo);
            $this->assertEquals(2, count($response));
            $this->assertEquals(
                base64_encode($fileInfo->getInitializationVector()),
                $response['initialization_vector']
            );
            $this->assertEquals(
                $storeManager->getStore()->getBaseUrl(
                    UrlInterface::URL_TYPE_MEDIA
                ) . $fileInfo->getPath(),
                $response['url']
            );
        }
    }

    /**
     * @return bool
     */
    private function isTestBaseUrlSecure()
    {
        return strpos('https://', TESTS_BASE_URL) !== false;
    }
}
