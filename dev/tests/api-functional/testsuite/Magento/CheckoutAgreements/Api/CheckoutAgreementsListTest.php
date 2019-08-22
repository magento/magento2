<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Api;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class CheckoutAgreementsListTest extends WebapiAbstract
{
    /**
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_active_with_html_content.php
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_inactive_with_text_content.php
     */
    public function testGetList()
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()->create(SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->setCurrentPage(1);
        $requestData = ['searchCriteria' => $searchCriteriaBuilder->create()->__toArray()];

        // Checkout agreements are disabled by default
        $agreements = $this->_webApiCall($this->getServiceInfo($requestData), $requestData);
        $this->assertEquals(2, count($agreements));
    }

    /**
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_active_with_html_content.php
     * @magentoApiDataFixture Magento/CheckoutAgreements/_files/agreement_inactive_with_text_content.php
     */
    public function testGetActiveAgreement()
    {
        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = Bootstrap::getObjectManager()->create(FilterBuilder::class);
        $filter = $filterBuilder->setField('is_active')->setValue(1)->create();

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()->create(SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->setCurrentPage(1);
        $searchCriteriaBuilder->addFilters([$filter]);
        $requestData = ['searchCriteria' => $searchCriteriaBuilder->create()->__toArray()];

        $agreements = $this->_webApiCall($this->getServiceInfo($requestData), $requestData);

        $this->assertEquals(1, count($agreements));
        $this->assertEquals(1, $agreements[0]['is_active']);
        $this->assertEquals('Checkout Agreement (active)', $agreements[0]['name']);
    }

    /**
     * @param array $requestData
     * @return array
     */
    private function getServiceInfo(array $requestData) : array
    {
        return [
            'soap' => [
                'service' => 'checkoutAgreementsCheckoutAgreementsListV1',
                'serviceVersion' => 'V1',
                'operation' => 'checkoutAgreementsCheckoutAgreementsListV1getList',
            ],
            'rest' => [
                'resourcePath' => '/V1/carts/licence/list' . '?' . http_build_query($requestData),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
        ];
    }
}
