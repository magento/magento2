<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Magento\Webapi\Model\ServiceMetadata;
use Magento\Webapi\Model\Config\ClassReflector;
use Magento\Webapi\Model\Config;

class ServiceMetadataTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ServiceMetadata
     */
    private $unit;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ClassReflector
     */
    private $classReflector;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->config = $this->createMock(Config::class);
        $this->classReflector = $this->createMock(ClassReflector::class);

        $this->unit = $this->objectManager->getObject(
            ServiceMetadata::class,
            [
                'config' => $this->config,
                'classReflector' => $this->classReflector
            ]
        );
    }

    public function testInitRoutesMetadata()
    {
        $this->classReflector->method('reflectClassMethods')
            ->willReturn(
                [
                    'save' => [],
                    'getById' => [],
                    'getList' => [],
                    'deleteById' => [],
                ]
            );
        $services = [
            'routes' =>
                [
                    '/V1/customers/:customerId' =>
                        [
                            'GET' =>
                                [
                                    'service' =>
                                        [
                                            'class' => 'Magento\\Customer\\Api\\CustomerRepositoryInterface',
                                            'method' => 'getById',
                                        ],
                                    'resources' =>
                                        [
                                            'Magento_Customer::customer' => true,
                                        ],
                                    'parameters' => [],
                                ],
                            'PUT' =>
                                [
                                    'service' =>
                                        [
                                            'class' => 'Magento\\Customer\\Api\\CustomerRepositoryInterface',
                                            'method' => 'save',
                                        ],
                                    'resources' =>
                                        [
                                            'Magento_Customer::manage' => true,
                                        ],
                                    'parameters' => [],
                                ],
                            'DELETE' =>
                                [
                                    'service' =>
                                        [
                                            'class' => 'Magento\\Customer\\Api\\CustomerRepositoryInterface',
                                            'method' => 'deleteById',
                                        ],
                                    'resources' =>
                                        [
                                            'Magento_Customer::manage' => true,
                                        ],
                                    'parameters' => [],
                                ],
                        ],
                    '/V1/customers/me' =>
                        [
                            'PUT' =>
                                [
                                    'service' =>
                                        [
                                            'class' => 'Magento\\Customer\\Api\\CustomerRepositoryInterface',
                                            'method' => 'save',
                                        ],
                                    'resources' =>
                                        [
                                            'self' => true,
                                        ],
                                    'parameters' =>
                                        [
                                            'customer.id' =>
                                                [
                                                    'force' => true,
                                                    'value' => '%customer_id%',
                                                ],
                                        ],
                                ],
                            'GET' =>
                                [
                                    'service' =>
                                        [
                                            'class' => 'Magento\\Customer\\Api\\CustomerRepositoryInterface',
                                            'method' => 'getById',
                                        ],
                                    'resources' =>
                                        [
                                            'self' => true,
                                        ],
                                    'parameters' =>
                                        [
                                            'customerId' =>
                                                [
                                                    'force' => true,
                                                    'value' => '%customer_id%',
                                                ],
                                        ],
                                ],
                        ],
                    '/V1/customers/search' =>
                        [
                            'GET' =>
                                [
                                    'service' =>
                                        [
                                            'class' => 'Magento\\Customer\\Api\\CustomerRepositoryInterface',
                                            'method' => 'getList',
                                        ],
                                    'resources' =>
                                        [
                                            'Magento_Customer::customer' => true,
                                        ],
                                    'parameters' => [],
                                ],
                        ],
                ],
            'services' =>
                [
                    'Magento\\Customer\\Api\\CustomerRepositoryInterface' =>
                        [
                            'V1' =>
                                [
                                    'methods' =>
                                        [
                                            'getById' =>
                                                [
                                                    'resources' =>
                                                        [
                                                            0 => 'Magento_Customer::customer',
                                                        ],
                                                    'realMethod' => 'getById',
                                                    'secure' => false,
                                                    'parameters' => [],
                                                ],
                                            'save' =>
                                                [
                                                    'resources' =>
                                                        [
                                                            0 => 'Magento_Customer::manage',
                                                        ],
                                                    'realMethod' => 'save',
                                                    'secure' => false,
                                                    'parameters' => [],
                                                ],
                                            'saveSelf' =>
                                                [
                                                    'resources' =>
                                                        [
                                                            0 => 'self',
                                                        ],
                                                    'realMethod' => 'save',
                                                    'secure' => false,
                                                    'parameters' =>
                                                        [
                                                            'customer.id' =>
                                                                [
                                                                    'force' => true,
                                                                    'value' => '%customer_id%',
                                                                ],
                                                        ],
                                                ],
                                            'getSelf' =>
                                                [
                                                    'resources' =>
                                                        [
                                                            0 => 'self',
                                                        ],
                                                    'realMethod' => 'getById',
                                                    'secure' => false,
                                                    'parameters' =>
                                                        [
                                                            'customerId' =>
                                                                [
                                                                    'force' => true,
                                                                    'value' => '%customer_id%',
                                                                ],
                                                        ],
                                                ],
                                            'getList' =>
                                                [
                                                    'resources' =>
                                                        [
                                                            0 => 'Magento_Customer::customer',
                                                        ],
                                                    'realMethod' => 'getList',
                                                    'secure' => false,
                                                    'parameters' => [],
                                                ],
                                            'deleteById' =>
                                                [
                                                    'resources' =>
                                                        [
                                                            0 => 'Magento_Customer::manage',
                                                        ],
                                                    'realMethod' => 'deleteById',
                                                    'secure' => false,
                                                    'parameters' => [],
                                                ],
                                        ],
                                ],
                        ],
                ],
        ];
        $this->config->method('getServices')
            ->willReturn($services);

        $result = $this->unit->getRoutesConfig();
        $assertedRoute = $result['customerCustomerRepositoryV1']['routes']['/V1/customers/me'];
        self::assertArrayHasKey('PUT', $assertedRoute);
        self::assertEquals('saveSelf', $assertedRoute['PUT']['method']);
        self::assertArrayHasKey('GET', $assertedRoute);
        self::assertEquals('getSelf', $assertedRoute['GET']['method']);
    }
}
