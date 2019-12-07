<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller;

/**
 * Test for Magento\Webapi\Controller\Soap class.
 */
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

    /**
     * Get the public wsdl with anonymous credentials
     *
     * @return void
     */
    public function testDispatchWsdlRequest(): void
    {
        $request = $this->objectManager->get(\Magento\Framework\Webapi\Request::class);
        $request->setParam(\Magento\Webapi\Model\Soap\Server::REQUEST_PARAM_LIST_WSDL, true);
        $response = $this->soapController->dispatch($request);
        $decodedWsdl = json_decode($response->getContent(), true);

        $this->assertWsdlServices($decodedWsdl);
    }

    /**
     * Check wsdl available methods.
     *
     * @param array $decodedWsdl
     *
     * @return void
     */
    protected function assertWsdlServices(array $decodedWsdl): void
    {
        $this->assertArrayHasKey('customerAccountManagementV1', $decodedWsdl);
        $this->assertArrayHasKey('integrationAdminTokenServiceV1', $decodedWsdl);
    }
}
