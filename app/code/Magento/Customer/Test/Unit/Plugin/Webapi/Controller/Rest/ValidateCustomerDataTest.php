<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Plugin\Webapi\Controller\Rest;

use Exception;
use Magento\Customer\Plugin\Webapi\Controller\Rest\ValidateCustomerData;
use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit test for ValidateCustomerData plugin
 */
class ValidateCustomerDataTest extends TestCase
{

    /**
     * @var ValidateCustomerData
     */
    private $validateCustomerDataObject;

    /**
     * @var ReflectionClass
     *
     */
    private $reflectionObject;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->validateCustomerDataObject = ObjectManager::getInstance()->get(ValidateCustomerData::class);
        $this->reflectionObject = new ReflectionClass(get_class($this->validateCustomerDataObject));
    }

    /**
     * Test if the customer Info is valid
     *
     * @param array $customerInfo
     * @param array $result
     * @dataProvider dataProviderInputData
     * @throws Exception
     */
    public function testValidateInputData(array $customerInfo, array $result)
    {
        $this->assertEquals(
            $result,
            $this->invokeValidateInputData('validateInputData', [$customerInfo])
        );
    }

    /**
     * @param string $methodName
     * @param array $arguments
     * @return mixed
     * @throws Exception
     */
    private function invokeValidateInputData(string $methodName, array $arguments = [])
    {
        $validateInputDataMethod = $this->reflectionObject->getMethod($methodName);
        $validateInputDataMethod->setAccessible(true);
        return $validateInputDataMethod->invokeArgs($this->validateCustomerDataObject, $arguments);
    }

    /**
     * @return array
     */
    public function dataProviderInputData(): array
    {
        return [
            [
                ['customer' => [
                        'id' => -1,
                        'Id' => 1,
                        'name' => [
                                'firstName' => 'Test',
                                'LastName' => 'user'
                            ],
                        'isHavingOwnHouse' => 1,
                        'address' => [
                                'street' => '1st Street',
                                'Street' => '3rd Street',
                                'city' => 'London'
                            ],
                    ]
                ],
                ['customer' => [
                        'id' => -1,
                        'name' => [
                                'firstName' => 'Test',
                                'LastName' => 'user'
                            ],
                        'isHavingOwnHouse' => 1,
                        'address' => [
                                'street' => '1st Street',
                                'city' => 'London'
                            ],
                    ]
                ],
                ['customer' => [
                    'id' => -1,
                    '_Id' => 1,
                    'name' => [
                        'firstName' => 'Test',
                        'LastName' => 'user'
                    ],
                    'isHavingOwnHouse' => 1,
                    'address' => [
                        'street' => '1st Street',
                        'city' => 'London'
                    ],
                ]
                ],
            ]
        ];
    }
}
