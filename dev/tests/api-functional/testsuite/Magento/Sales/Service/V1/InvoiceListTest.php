<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config;

/**
 * Class InvoiceListTest
 */
class InvoiceListTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/invoices';

    const SERVICE_READ_NAME = 'salesInvoiceRepositoryV1';

    const SERVICE_VERSION = 'V1';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/invoice.php
     */
    public function testInvoiceList()
    {
        /** @var $searchCriteriaBuilder  \Magento\Framework\Api\SearchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->create(
            'Magento\Framework\Api\SearchCriteriaBuilder'
        );

        /** @var $filterBuilder  \Magento\Framework\Api\FilterBuilder */
        $filterBuilder = $this->objectManager->create(
            'Magento\Framework\Api\FilterBuilder'
        );

        $searchCriteriaBuilder->addFilter(
            [
                $filterBuilder
                    ->setField('state')
                    ->setValue(2)
                    ->create(),
            ]
        );
        $searchData = $searchCriteriaBuilder->create()->__toArray();

        $requestData = ['criteria' => $searchData];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($requestData),
                'httpMethod' => Config::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'getList',
            ],
        ];

        $result = $this->_webApiCall($serviceInfo, $requestData);
        // TODO Test fails, due to the inability of the framework API to handle data collection
        $this->assertArrayHasKey('items', $result);
        $this->assertCount(1, $result['items']);
    }
}
