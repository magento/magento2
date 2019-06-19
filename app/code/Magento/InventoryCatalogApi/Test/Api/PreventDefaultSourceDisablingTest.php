<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogApi\Test\Api;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;

class PreventDefaultSourceDisablingTest extends WebapiAbstract
{
    /**#@+
     * Service constants
     */
    const RESOURCE_PATH = '/V1/inventory/sources';
    const SERVICE_NAME = 'inventoryApiSourceRepositoryV1';
    /**#@-*/

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->defaultSourceProvider = Bootstrap::getObjectManager()->get(DefaultSourceProviderInterface::class);
    }

    /**
     * @throws \Exception
     */
    public function testPreventDefaultSourceDisabling(): void
    {
        $this->_markTestAsRestOnly('https://github.com/magento-engcom/msi/issues/2326');
        $defaultSourceCode = $this->defaultSourceProvider->getCode();
        $data = [
            SourceInterface::SOURCE_CODE => $defaultSourceCode, // needed for SOAP mode
            SourceInterface::NAME => 'source-name-1',
            SourceInterface::POSTCODE => 'source-postcode',
            SourceInterface::COUNTRY_ID => 'US',
            SourceInterface::ENABLED => false,
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $defaultSourceCode,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $expectedErrorData = [
            'message' => 'Validation Failed',
            'errors' => [
                [
                    'message' => 'Default source can not be disabled.',
                    'parameters' => [],
                ],
            ],
        ];
        $this->webApiCall($serviceInfo, $data, $expectedErrorData);
    }

    /**
     * @param array $serviceInfo
     * @param array $data
     * @param array $expectedErrorData
     * @return void
     * @throws \Exception
     */
    private function webApiCall(array $serviceInfo, array $data, array $expectedErrorData)
    {
        try {
            $this->_webApiCall($serviceInfo, ['source' => $data]);
            $this->fail('Expected throwing exception');
        } catch (\Exception $e) {
            if (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST) {
                self::assertEquals($expectedErrorData, $this->processRestExceptionResult($e));
                self::assertEquals(Exception::HTTP_BAD_REQUEST, $e->getCode());
            } elseif (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
                $this->assertInstanceOf('SoapFault', $e);
                $expectedWrappedErrors = [];
                foreach ($expectedErrorData['errors'] as $error) {
                    // @see \Magento\TestFramework\TestCase\WebapiAbstract::getActualWrappedErrors()
                    $expectedWrappedErrors[] = [
                        'message' => $error['message'],
                        'params' => $error['parameters'],
                    ];
                }
                $this->checkSoapFault($e, $expectedErrorData['message'], 'env:Sender', [], $expectedWrappedErrors);
            } else {
                throw $e;
            }
        }
    }
}
