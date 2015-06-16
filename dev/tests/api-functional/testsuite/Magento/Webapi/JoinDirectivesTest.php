<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SearchCriteria;

class JoinDirectivesTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->searchBuilder = $objectManager->create('Magento\Framework\Api\SearchCriteriaBuilder');
        $this->sortOrderBuilder = $objectManager->create('Magento\Framework\Api\SortOrderBuilder');
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/quote.php
     */
    public function testGetList()
    {
        /** @var SortOrder $sortOrder */
        $sortOrder = $this->sortOrderBuilder->setField('store_id')->setDirection(SearchCriteria::SORT_ASC)->create();
        $this->searchBuilder->setSortOrders([$sortOrder]);
        $searchCriteria = $this->searchBuilder->create()->__toArray();
        $requestData = ['searchCriteria' => $searchCriteria];

        $restResourcePath = '/V1/TestJoinDirectives/';
        $soapService = 'testJoinDirectivesTestRepositoryV1';

        $serviceInfo = [
            'rest' => [
                'resourcePath' => $restResourcePath . '?' . http_build_query($requestData),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => $soapService,
                'operation' => $soapService . 'GetList',
            ],
        ];
        $searchResult = $this->_webApiCall($serviceInfo, $requestData);

        $expectedExtensionAttributes = [
            'firstname' => 'Admin',
            'lastname' => 'Admin',
            'email' => 'admin@example.com'
        ];

        $this->assertArrayHasKey('items', $searchResult);
        $cartData = array_pop($searchResult['items']);
        $testAttribute = $cartData['extension_attributes']['quote_test_attribute'];
        $this->assertEquals($expectedExtensionAttributes['firstname'], $testAttribute['first_name']);
        $this->assertEquals($expectedExtensionAttributes['lastname'], $testAttribute['last_name']);
        $this->assertEquals($expectedExtensionAttributes['email'], $testAttribute['email']);
    }
}
