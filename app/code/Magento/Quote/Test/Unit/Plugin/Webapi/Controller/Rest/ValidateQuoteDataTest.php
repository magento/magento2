<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Plugin\Webapi\Controller\Rest;

use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Plugin\Webapi\Controller\Rest\ValidateQuoteData;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit test for ValidateQuoteData plugin
 */
class ValidateQuoteDataTest extends TestCase
{

    /**
     * @var ValidateQuoteData
     */
    private $validateQuoteDataObject;

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
        $this->validateQuoteDataObject = ObjectManager::getInstance()->get(ValidateQuoteData::class);
        $this->reflectionObject = new ReflectionClass(get_class($this->validateQuoteDataObject));
    }
    /**
     * Test if the quote array is valid
     *
     * @param array $array
     * @param array $result
     * @dataProvider dataProviderInputData
     * @throws Exception
     */
    public function testValidateInputData(array $array, array $result)
    {
        $this->assertEquals(
            $result,
            $this->invokeValidateInputData('validateInputData', [$array])
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
        return $validateInputDataMethod->invokeArgs($this->validateQuoteDataObject, $arguments);
    }

    /**
     * @return array
     */
    public function dataProviderInputData(): array
    {
        return [
            [
                ['person' =>
                    [
                        'id' => -1,
                        'Id' => 1,
                        'name' =>
                            [
                                'firstName' => 'John',
                                'LastName' => 'S'
                            ],
                        'isHavingVehicle' => 1,
                        'address' =>
                            [
                                'street' => '4th Street',
                                'Street' => '2nd Street',
                                'city' => 'Atlanta'
                            ],
                    ]
                ],
                ['person' =>
                    [
                        'id' => -1,
                        'name' =>
                            [
                                'firstName' => 'John',
                                'LastName' => 'S'
                            ],
                        'isHavingVehicle' => 1,
                        'address' =>
                            [
                                'street' => '4th Street',
                                'city' => 'Atlanta'
                            ],
                    ]
                ],
            ]
        ];
    }
}
