<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Api\StockRepository;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;

class PreventDefaultStockDeletingTest extends WebapiAbstract
{
    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    protected function setUp()
    {
        parent::setUp();
        $this->defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProviderInterface::class);
    }

    /**
     * @throws \Exception
     */
    public function testCouldNotDeleteException()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/inventory/stocks/' . $this->defaultStockProvider->getId(),
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => 'inventoryApiStockRepositoryV1',
                'operation' => 'inventoryApiStockRepositoryV1DeleteById',
            ],
        ];
        $expectedMessage = 'Default Stock could not be deleted.';
        try {
            (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST) ? $this->_webApiCall($serviceInfo) :
                $this->_webApiCall($serviceInfo, ['stockId' => $this->defaultStockProvider->getId()]);
            $this->fail('Expected throwing exception');
        } catch (\Exception $e) {
            if (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST) {
                $errorData = $this->processRestExceptionResult($e);
                self::assertEquals($expectedMessage, $errorData['message']);
                self::assertEquals(Exception::HTTP_BAD_REQUEST, $e->getCode());
            } elseif (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
                $this->assertInstanceOf('SoapFault', $e);
                $this->checkSoapFault($e, $expectedMessage, 'env:Sender');
            } else {
                throw $e;
            }
        }
    }
}
