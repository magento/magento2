<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller;

class SoapTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Webapi\Controller\Soap
     */
    protected $soapController;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->soapController = $this->objectManager->get(\Magento\Webapi\Controller\Soap::class);
    }

    /*
     * Get the public wsdl with anonymous credentials
     */
    public function testDispatchWsdlRequest()
    {
        $request = $this->objectManager->get(\Magento\Framework\Webapi\Request::class);
        $request->setParam(\Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_LIST_WSDL, true);
        $response = $this->soapController->dispatch($request);
        $decoded_wsdl = json_decode($response->getContent(), true);
        $this->assertArrayHasKey("customerAccountManagementV1", $decoded_wsdl);
        $this->assertArrayHasKey("integrationAdminTokenServiceV1", $decoded_wsdl);
    }
}
