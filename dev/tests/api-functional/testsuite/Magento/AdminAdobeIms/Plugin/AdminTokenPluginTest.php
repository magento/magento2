<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * api-functional test for \Magento\AdminAdobeIms\Plugin\AdminTokenPlugin.
 */
class AdminTokenPluginTest extends WebapiAbstract
{
    private const RESOURCE_PATH_ADMIN_TOKEN = "/V1/integration/admin/token";

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Setup AdminTokenPlugin
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->_markTestAsRestOnly();
        $objectManager = Bootstrap::getObjectManager();
        $this->configWriter = $objectManager->get(WriterInterface::class);
        $this->scopeConfig = $objectManager->get(ScopeConfigInterface::class);
    }
    /**
     * @magentoApiDataFixture Magento/Webapi/_files/webapi_user.php
     */
    public function testAdminTokenGenerationDisabled()
    {
        $adminUserNameFromFixture = 'webapi_user';

        $this->configWriter->save(ImsConfig::XML_PATH_ENABLED, 1);
        $this->scopeConfig->clean();

        $noExceptionOccurred = false;
        try {
            $serviceInfo = [
                'rest' => [
                    'resourcePath' => self::RESOURCE_PATH_ADMIN_TOKEN,
                    'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
                ],
            ];
            $requestData = [
                'username' => $adminUserNameFromFixture,
                'password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
            ];
            $this->_webApiCall($serviceInfo, $requestData);
            $noExceptionOccurred = true;
        } catch (\Exception $exception) {
            $exceptionData = $this->processRestExceptionResult($exception);
            $expectedExceptionData = [
                'message' => "Admin token generation is disabled. Please use Adobe IMS ACCESS_TOKEN."
            ];
            $this->assertEquals($expectedExceptionData, $exceptionData, "Exception message is invalid.");
        }
        if ($noExceptionOccurred) {
            $this->fail("Exception was expected to be thrown when Admin Adobe Ims module is enabled.");
        }

        $this->configWriter->save(ImsConfig::XML_PATH_ENABLED, 0);
        $this->scopeConfig->clean();
    }
}
