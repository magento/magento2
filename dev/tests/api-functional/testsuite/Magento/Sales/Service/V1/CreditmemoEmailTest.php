<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Service\V1;

use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test API call /creditmemo/{id}/emails
 */
class CreditmemoEmailTest extends WebapiAbstract
{
    private const SERVICE_VERSION = 'V1';

    private const SERVICE_NAME = 'salesCreditmemoManagementV1';

    /**
     * @magentoApiDataFixture Magento/Sales/_files/creditmemo_with_list.php
     */
    public function testCreditmemoEmail()
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var Collection $creditmemoCollection */
        $creditmemoCollection = $objectManager->get(
            Collection::class
        );
        $creditmemo = $creditmemoCollection->getFirstItem();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/creditmemo/' . $creditmemo->getId() . '/emails',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'notify',
            ],
        ];
        $requestData = ['id' => $creditmemo->getId()];
        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertTrue($result);
    }
}
