<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\WebapiAsync\Plugin;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Webapi\Model\ServiceMetadata;
use Magento\WebapiAsync\Controller\Rest\AsynchronousSchemaRequestProcessor;
use Magento\WebapiAsync\Controller\Rest\AsynchronousSchemaRequestProcessorMock;

class ServiceMetadataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ServiceMetadata
     */
    private $serviceMetadata;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->configure([
            'preferences' => [
                AsynchronousSchemaRequestProcessor::class => AsynchronousSchemaRequestProcessorMock::class
            ]
        ]);

        $this->serviceMetadata = $objectManager->create(ServiceMetadata::class);
    }

    public function testGetServiceMetadata()
    {
        $expected = [
            'methods' => [
                'activate' => [
                    'method' => 'activate',
                    'inputRequired' => false,
                    'isSecure' => false,
                    'resources' => [
                        'Magento_Customer::manage'
                    ],
                    'documentation' => 'Activate a customer account using a key that was sent in a confirmation email.',
                    'interface' => [
                        'in' => [
                            'parameters' => [
                                'email' => [
                                    'type' => 'string',
                                    'required' => true,
                                    'documentation' => null
                                ],
                                'confirmationKey' => [
                                    'type' => 'string',
                                    'required' => true,
                                    'documentation' => null
                                ]
                            ]
                        ],
                        'out' => [
                            'parameters' => [
                                'result' => [
                                    'type' => 'AsynchronousOperationsDataAsyncResponseInterface',
                                    'required' => true,
                                    'documentation' => 'Returns response information for the asynchronous request.',
                                    'response_codes' => [
                                        'success' => [
                                            'code' => '202',
                                            'description' => '202 Accepted.'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'class' => AccountManagementInterface::class,
            'description' => 'Interface for managing customers accounts.',
        ];
        $actual = $this->serviceMetadata->getServiceMetadata('customerAccountManagementV1');
        $this->assertEquals(array_replace_recursive($actual, $expected), $actual);
    }
}
