<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi;

use Magento\Framework\Api\SearchCriteriaBuilder;

class JoinDirectivesTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    /**
     * @var string
     */
    protected $_version;

    /**
     * @var string
     */
    protected $_restResourcePath;

    /**
     * @var string
     */
    protected $_soapService;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchBuilder;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_version = 'V1';
        $this->_restResourcePath = "/{$this->_version}/TestJoinDirectives/";
        $this->_soapService = "testJoinDirectivesTestRepository{$this->_version}";
        $this->searchBuilder = $objectManager->create(
            'Magento\Framework\Api\SearchCriteriaBuilder'
        );
    }

    public function testGetList()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => $this->_restResourcePath,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => $this->_soapService,
                'operation' => $this->_soapService . 'GetList',
            ],
        ];
        $searchCriteria = $this->searchBuilder->create()->__toArray();
        $requestData = ['searchCriteria' => $searchCriteria];
        $searchResult = $this->_webApiCall($serviceInfo, $requestData);
    }
}
