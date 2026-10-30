<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\SerializationException;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Integration coverage for ServiceInputProcessor handling of malformed complex-type input.
 *
 * Complements REST api-functional tests for GitHub issue #31551 (P0): scalar/non-array
 * values where a complex type is expected must throw SerializationException / WebapiException
 * (client 4xx), while empty object `{}` / `[]` remains accepted.
 *
 * @see \Magento\Framework\Webapi\ServiceInputProcessor
 */
class ServiceInputProcessorMalformedInputTest extends TestCase
{
    /**
     * @var ServiceInputProcessor
     */
    private $processor;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->processor = Bootstrap::getObjectManager()->get(ServiceInputProcessor::class);
    }

    /**
     * process() must reject scalar values for complex service parameters as WebapiException.
     *
     * @param mixed $customerValue
     */
    #[DataProvider('invalidScalarComplexInputDataProvider')]
    public function testProcessRejectsScalarForComplexCustomerParameter($customerValue): void
    {
        $this->expectException(WebapiException::class);

        $this->processor->process(
            CustomerRepositoryInterface::class,
            'save',
            ['customer' => $customerValue]
        );
    }

    /**
     * convertValue() must throw SerializationException for scalar complex-type input.
     *
     * @param mixed $value
     */
    #[DataProvider('invalidScalarComplexInputDataProvider')]
    public function testConvertValueRejectsScalarForComplexType($value): void
    {
        $this->expectException(SerializationException::class);

        $this->processor->convertValue($value, CustomerInterface::class);
    }

    /**
     * Empty array (JSON object `{}`) for a complex type must still create a data object.
     */
    public function testConvertValueAcceptsEmptyArrayForComplexType(): void
    {
        $result = $this->processor->convertValue([], CustomerInterface::class);

        $this->assertInstanceOf(CustomerInterface::class, $result);
    }

    /**
     * process() with empty customer object must not throw type-shape errors.
     *
     * Further domain validation may still fail for missing required customer fields;
     * this only asserts the empty object is accepted as a complex type shape.
     */
    public function testProcessAcceptsEmptyArrayForComplexCustomerParameter(): void
    {
        try {
            $result = $this->processor->process(
                CustomerRepositoryInterface::class,
                'save',
                ['customer' => []]
            );
            $this->assertIsArray($result);
            $this->assertInstanceOf(CustomerInterface::class, $result[0]);
        } catch (SerializationException $e) {
            $this->fail(
                'Empty complex-type object must not raise SerializationException: ' . $e->getMessage()
            );
        } catch (WebapiException $e) {
            // Domain-level InputException wrapped as WebapiException is acceptable;
            // type-shape rejection would mention invalid type.
            $this->assertStringNotContainsString(
                "value's type is invalid",
                $e->getMessage(),
                'Empty complex-type object must not be rejected as invalid type shape'
            );
        }
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function invalidScalarComplexInputDataProvider(): array
    {
        return [
            'empty string' => [''],
            'non-object string' => ['not-an-object'],
            'integer' => [1],
            'boolean' => [true],
            'float' => [1.5],
        ];
    }
}
