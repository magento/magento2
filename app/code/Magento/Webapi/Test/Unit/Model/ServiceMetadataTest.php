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
use ReflectionProperty;

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
        $servicesProperty = new ReflectionProperty($this->unit, 'services');
        $servicesProperty->setAccessible(true);
        $servicesProperty->setValue($this->unit, null);

        $this->classReflector->method('reflectClassMethods')
            ->willReturn(
            array (
                'save' =>
                    array (
                        'documentation' => 'Create or update a customer.',
                        'interface' =>
                            array (
                                'in' =>
                                    array (
                                        'parameters' =>
                                            array (
                                                'customer' =>
                                                    array (
                                                        'type' => 'CustomerDataCustomerInterface',
                                                        'required' => true,
                                                        'documentation' => '',
                                                    ),
                                                'passwordHash' =>
                                                    array (
                                                        'type' => 'string',
                                                        'required' => false,
                                                        'documentation' => '',
                                                        'default' => NULL,
                                                    ),
                                            ),
                                    ),
                                'out' =>
                                    array (
                                        'parameters' =>
                                            array (
                                                'result' =>
                                                    array (
                                                        'type' => 'CustomerDataCustomerInterface',
                                                        'documentation' => '',
                                                        'required' => true,
                                                    ),
                                            ),
                                        'throws' =>
                                            array (
                                                0 => '\\Magento\\Framework\\Exception\\InputException',
                                                1 => '\\Magento\\Framework\\Exception\\State\\InputMismatchException',
                                                2 => '\\Magento\\Framework\\Exception\\LocalizedException',
                                            ),
                                    ),
                            ),
                    ),
                'getById' =>
                    array (
                        'documentation' => 'Get customer by Customer ID.',
                        'interface' =>
                            array (
                                'in' =>
                                    array (
                                        'parameters' =>
                                            array (
                                                'customerId' =>
                                                    array (
                                                        'type' => 'int',
                                                        'required' => true,
                                                        'documentation' => '',
                                                    ),
                                            ),
                                    ),
                                'out' =>
                                    array (
                                        'parameters' =>
                                            array (
                                                'result' =>
                                                    array (
                                                        'type' => 'CustomerDataCustomerInterface',
                                                        'documentation' => '',
                                                        'required' => true,
                                                    ),
                                            ),
                                        'throws' =>
                                            array (
                                                0 => '\\Magento\\Framework\\Exception\\NoSuchEntityException',
                                                1 => '\\Magento\\Framework\\Exception\\LocalizedException',
                                            ),
                                    ),
                            ),
                    ),
                'getList' =>
                    array (
                        'documentation' => 'Retrieve customers which match a specified criteria. This call returns an array of objects, but detailed information about each object’s attributes might not be included. See https://devdocs.magento.com/codelinks/attributes.html#CustomerRepositoryInterface to determine which call to use to get detailed information about all attributes for an object.',
                        'interface' =>
                            array (
                                'in' =>
                                    array (
                                        'parameters' =>
                                            array (
                                                'searchCriteria' =>
                                                    array (
                                                        'type' => 'FrameworkSearchCriteriaInterface',
                                                        'required' => true,
                                                        'documentation' => '',
                                                    ),
                                            ),
                                    ),
                                'out' =>
                                    array (
                                        'parameters' =>
                                            array (
                                                'result' =>
                                                    array (
                                                        'type' => 'CustomerDataCustomerSearchResultsInterface',
                                                        'documentation' => '',
                                                        'required' => true,
                                                    ),
                                            ),
                                        'throws' =>
                                            array (
                                                0 => '\\Magento\\Framework\\Exception\\LocalizedException',
                                            ),
                                    ),
                            ),
                    ),
                'deleteById' =>
                    array (
                        'documentation' => 'Delete customer by Customer ID.',
                        'interface' =>
                            array (
                                'in' =>
                                    array (
                                        'parameters' =>
                                            array (
                                                'customerId' =>
                                                    array (
                                                        'type' => 'int',
                                                        'required' => true,
                                                        'documentation' => '',
                                                    ),
                                            ),
                                    ),
                                'out' =>
                                    array (
                                        'parameters' =>
                                            array (
                                                'result' =>
                                                    array (
                                                        'type' => 'boolean',
                                                        'documentation' => 'true on success',
                                                        'required' => true,
                                                    ),
                                            ),
                                        'throws' =>
                                            array (
                                                0 => '\\Magento\\Framework\\Exception\\NoSuchEntityException',
                                                1 => '\\Magento\\Framework\\Exception\\LocalizedException',
                                            ),
                                    ),
                            ),
                    ),
            )
        );

        $this->config->method('getServices')
            ->willReturn(
                array(
                    'routes' =>
                        array(
                            '/V1/customers/:customerId' =>
                                array(
                                    'GET' =>
                                        array(
                                            'secure' => false,
                                            'service' =>
                                                array(
                                                    'class' => 'Magento\\Customer\\Api\\CustomerRepositoryInterface',
                                                    'method' => 'getById',
                                                ),
                                            'resources' =>
                                                array(
                                                    'Magento_Customer::customer' => true,
                                                ),
                                            'parameters' =>
                                                array(),
                                        ),
                                    'PUT' =>
                                        array(
                                            'secure' => false,
                                            'service' =>
                                                array(
                                                    'class' => 'Magento\\Customer\\Api\\CustomerRepositoryInterface',
                                                    'method' => 'save',
                                                ),
                                            'resources' =>
                                                array(
                                                    'Magento_Customer::manage' => true,
                                                ),
                                            'parameters' =>
                                                array(),
                                        ),
                                    'DELETE' =>
                                        array(
                                            'secure' => false,
                                            'service' =>
                                                array(
                                                    'class' => 'Magento\\Customer\\Api\\CustomerRepositoryInterface',
                                                    'method' => 'deleteById',
                                                ),
                                            'resources' =>
                                                array(
                                                    'Magento_Customer::manage' => true,
                                                ),
                                            'parameters' =>
                                                array(),
                                        ),
                                ),
                            '/V1/customers/me' =>
                                array(
                                    'PUT' =>
                                        array(
                                            'secure' => false,
                                            'service' =>
                                                array(
                                                    'class' => 'Magento\\Customer\\Api\\CustomerRepositoryInterface',
                                                    'method' => 'save',
                                                ),
                                            'resources' =>
                                                array(
                                                    'self' => true,
                                                ),
                                            'parameters' =>
                                                array(
                                                    'customer.id' =>
                                                        array(
                                                            'force' => true,
                                                            'value' => '%customer_id%',
                                                        ),
                                                ),
                                        ),
                                    'GET' =>
                                        array(
                                            'secure' => false,
                                            'service' =>
                                                array(
                                                    'class' => 'Magento\\Customer\\Api\\CustomerRepositoryInterface',
                                                    'method' => 'getById',
                                                ),
                                            'resources' =>
                                                array(
                                                    'self' => true,
                                                ),
                                            'parameters' =>
                                                array(
                                                    'customerId' =>
                                                        array(
                                                            'force' => true,
                                                            'value' => '%customer_id%',
                                                        ),
                                                ),
                                        ),
                                ),
                            '/V1/customers/search' =>
                                array(
                                    'GET' =>
                                        array(
                                            'secure' => false,
                                            'service' =>
                                                array(
                                                    'class' => 'Magento\\Customer\\Api\\CustomerRepositoryInterface',
                                                    'method' => 'getList',
                                                ),
                                            'resources' =>
                                                array(
                                                    'Magento_Customer::customer' => true,
                                                ),
                                            'parameters' =>
                                                array(),
                                        ),
                                ),
                        ),
                    'services' =>
                        array(
                            'Magento\\Customer\\Api\\CustomerRepositoryInterface' =>
                                array(
                                    'V1' =>
                                        array(
                                            'methods' =>
                                                array(
                                                    'getById' =>
                                                        array(
                                                            'resources' =>
                                                                array(
                                                                    0 => 'Magento_Customer::customer',
                                                                ),
                                                            'realMethod' => 'getById',
                                                            'secure' => false,
                                                            'parameters' =>
                                                                array(),
                                                        ),
                                                    'save' =>
                                                        array(
                                                            'resources' =>
                                                                array(
                                                                    0 => 'Magento_Customer::manage',
                                                                ),
                                                            'realMethod' => 'save',
                                                            'secure' => false,
                                                            'parameters' =>
                                                                array(),
                                                        ),
                                                    'saveSelf' =>
                                                        array(
                                                            'resources' =>
                                                                array(
                                                                    0 => 'self',
                                                                ),
                                                            'realMethod' => 'save',
                                                            'secure' => false,
                                                            'parameters' =>
                                                                array(
                                                                    'customer.id' =>
                                                                        array(
                                                                            'force' => true,
                                                                            'value' => '%customer_id%',
                                                                        ),
                                                                ),
                                                        ),
                                                    'getSelf' =>
                                                        array(
                                                            'resources' =>
                                                                array(
                                                                    0 => 'self',
                                                                ),
                                                            'realMethod' => 'getById',
                                                            'secure' => false,
                                                            'parameters' =>
                                                                array(
                                                                    'customerId' =>
                                                                        array(
                                                                            'force' => true,
                                                                            'value' => '%customer_id%',
                                                                        ),
                                                                ),
                                                        ),
                                                    'getList' =>
                                                        array(
                                                            'resources' =>
                                                                array(
                                                                    0 => 'Magento_Customer::customer',
                                                                ),
                                                            'realMethod' => 'getList',
                                                            'secure' => false,
                                                            'parameters' =>
                                                                array(),
                                                        ),
                                                    'deleteById' =>
                                                        array(
                                                            'resources' =>
                                                                array(
                                                                    0 => 'Magento_Customer::manage',
                                                                ),
                                                            'realMethod' => 'deleteById',
                                                            'secure' => false,
                                                            'parameters' =>
                                                                array(),
                                                        ),
                                                ),
                                        ),
                                ),
                        ),
                )
            );

        $result = $this->unit->getRoutesConfig();
        $assertedRoute = $result['customerCustomerRepositoryV1']['routes']['/V1/customers/me'];
        $this->assertArrayHasKey('PUT', $assertedRoute);
        $this->assertEquals('saveSelf', $assertedRoute['PUT']['method']);
        $this->assertArrayHasKey('GET', $assertedRoute);
        $this->assertEquals('getSelf', $assertedRoute['GET']['method']);
    }
}
