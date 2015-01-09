<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Service\V1\Agreement;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config as RestConfig;

class ReadServiceTest extends WebapiAbstract
{
    /**
     * @var array
     */
    private $listServiceInfo;

    protected function setUp()
    {
        $this->listServiceInfo = [
            'soap' => [
                'service' => 'checkoutAgreementsAgreementReadServiceV1',
                'serviceVersion' => 'V1',
                'operation' => 'checkoutAgreementsAgreementReadServiceV1GetList',
            ],
            'rest' => [
                'resourcePath' => '/V1/carts/licence/',
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
        ];
    }

    /**
     * Retrieve agreement by given name
     *
     * @param string $name
     * @return \Magento\CheckoutAgreements\Model\Agreement
     * @throws \InvalidArgumentException
     */
    protected function getAgreementByName($name)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $agreement \Magento\CheckoutAgreements\Model\Agreement */
        $agreement = $objectManager->create('Magento\CheckoutAgreements\Model\Agreement');
        $agreement->load($name, 'name');
        if (!$agreement->getId()) {
            throw new \InvalidArgumentException('There is no checkout agreement with provided ID.');
        }
        return $agreement;
    }

    /**
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_active_with_html_content.php
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_inactive_with_text_content.php
     */
    public function testGetListReturnsEmptyListIfCheckoutAgreementsAreDisabledOnFrontend()
    {
        // Checkout agreements are disabled by default
        $agreements = $this->_webApiCall($this->listServiceInfo, []);
        $this->assertEmpty($agreements);
    }

    /**
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_active_with_html_content.php
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_inactive_with_text_content.php
     */
    public function testGetListReturnsTheListOfActiveCheckoutAgreements()
    {
        // checkout/options/enable_agreements must be set to 1 in system configuration
        // @todo remove next statement when \Magento\TestFramework\TestCase\WebapiAbstract::_updateAppConfig is fixed
        $this->markTestIncomplete('This test relies on system configuration state.');
        $agreementModel = $this->getAgreementByName('Checkout Agreement (active)');

        $agreements = $this->_webApiCall($this->listServiceInfo, []);
        $this->assertCount(1, $agreements);
        $agreementData = $agreements[0];
        $this->assertEquals($agreementModel->getId(), $agreementData['id']);
        $this->assertEquals($agreementModel->getName(), $agreementData['name']);
        $this->assertEquals($agreementModel->getContent(), $agreementData['content']);
        $this->assertEquals($agreementModel->getContentHeight(), $agreementData['content_height']);
        $this->assertEquals($agreementModel->getCheckboxText(), $agreementData['checkbox_text']);
        $this->assertEquals($agreementModel->getIsActive(), $agreementData['active']);
        $this->assertEquals($agreementModel->getIsHtml(), $agreementData['html']);
    }
}
