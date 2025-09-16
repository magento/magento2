<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Plugin\ValidateDobOnSave;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for validate date of birth plugin
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidateDobOnSaveTest extends TestCase
{
    /** @var EavConfig&MockObject */
    private $eavConfig;

    /** @var JsonSerializer&MockObject */
    private $json;

    /** @var CustomerRepositoryInterface&MockObject */
    private $repo;

    /** @var ValidateDobOnSave */
    private $plugin;

    protected function setUp(): void
    {
        $this->eavConfig = $this->createMock(EavConfig::class);
        $this->json = $this->createMock(JsonSerializer::class);
        $this->repo = $this->createMock(CustomerRepositoryInterface::class);

        $this->plugin = new ValidateDobOnSave(
            $this->eavConfig,
            $this->json
        );
    }

    public function testInvalidDobStringThrows(): void
    {
        $customer = $this->createCustomerMock('not-a-date');
        $this->mockAttributeRulesArray(['date_range_min' => '1980-01-01', 'date_range_max' => '2000-12-31']);

        $called = false;
        $proceed = $this->proceedPlugin($called, $customer);

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Date of Birth is invalid.');
        $this->plugin->aroundSave($this->repo, $proceed, $customer, null);

        $this->assertFalse($called);
    }

    public function testDobBeforeMinThrows(): void
    {
        $customer = $this->createCustomerMock('1979-12-31');
        $this->mockAttributeRulesArray(['date_range_min' => '1980-01-01', 'date_range_max' => '2000-12-31']);

        $called = false;
        $proceed = $this->proceedPlugin($called, $customer);

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('on or after 1980-01-01');
        $this->plugin->aroundSave($this->repo, $proceed, $customer, null);

        $this->assertFalse($called);
    }

    public function testDobAfterMaxThrows(): void
    {
        $customer = $this->createCustomerMock('2001-01-01');
        $this->mockAttributeRulesArray(['date_range_min' => '1980-01-01', 'date_range_max' => '2000-12-31']);

        $called = false;
        $proceed = $this->proceedPlugin($called, $customer);

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('on or before 2000-12-31');
        $this->plugin->aroundSave($this->repo, $proceed, $customer, null);

        $this->assertFalse($called);
    }

    /**
     * @return void
     * @throws InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testDobWithinRangeCallsProceedAndReturnsResult(): void
    {
        $customer = $this->createCustomerMock('1990-06-15');
        $this->mockAttributeRulesArray(['date_range_min' => '1980-01-01', 'date_range_max' => '2000-12-31']);

        $result = $this->createMock(CustomerInterface::class);
        $called = false;
        $proceed = function (CustomerInterface $c, $hash = null) use (&$called, $result) {
            $called = true;
            return $result;
        };

        $actual = $this->plugin->aroundSave($this->repo, $proceed, $customer, null);

        $this->assertTrue($called);
        $this->assertSame($result, $actual);
    }

    public function testEmptyDobSkipsValidationAndProceeds(): void
    {
        $customer = $this->createCustomerMock(null);
        $this->mockAttributeRulesArray(['date_range_min' => '1980-01-01', 'date_range_max' => '2000-12-31']);

        $called = false;
        $proceed = $this->proceedPlugin($called, $customer);

        $actual = $this->plugin->aroundSave($this->repo, $proceed, $customer, null);

        $this->assertTrue($called);
        $this->assertSame($customer, $actual);
    }

    public function testRulesAsJsonStringAreUnserialized(): void
    {
        $customer = $this->createCustomerMock('1979-12-31');
        $rules = ['date_range_min' => '1980-01-01', 'date_range_max' => '2000-12-31'];
        $json = json_encode($rules);

        $attribute = $this->createAttributeMockForGetData('validate_rules', $json);
        $this->eavConfig->method('getAttribute')->with('customer', 'dob')->willReturn($attribute);
        $this->json->expects($this->once())->method('unserialize')->with($json)->willReturn($rules);

        $called = false;
        $proceed = $this->proceedPlugin($called, $customer);

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('on or after 1980-01-01');
        $this->plugin->aroundSave($this->repo, $proceed, $customer, null);

        $this->assertFalse($called);
    }

    public function testMillisecondRulesBeforeMinThrows(): void
    {
        $customer = $this->createCustomerMock('1979-12-31');
        $minMs = (new \DateTimeImmutable('1980-01-01', new \DateTimeZone('UTC')))->getTimestamp() * 1000;
        $maxMs = (new \DateTimeImmutable('2000-12-31', new \DateTimeZone('UTC')))->getTimestamp() * 1000;

        $this->mockAttributeRulesArray(['date_range_min' => $minMs, 'date_range_max' => $maxMs]);

        $called = false;
        $proceed = $this->proceedPlugin($called, $customer);

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('on or after 1980-01-01');
        $this->plugin->aroundSave($this->repo, $proceed, $customer, null);

        $this->assertFalse($called);
    }

    public function testDobAsMillisecondTimestampThrowsAgainstStringRule(): void
    {
        $dobMs = (new \DateTimeImmutable('1979-12-31', new \DateTimeZone('UTC')))->getTimestamp() * 1000;
        $customer = $this->createCustomerMock($dobMs);
        $this->mockAttributeRulesArray(['date_range_min' => '1980-01-01', 'date_range_max' => '2000-12-31']);

        $called = false;
        $proceed = $this->proceedPlugin($called, $customer);

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('on or after 1980-01-01');
        $this->plugin->aroundSave($this->repo, $proceed, $customer, null);

        $this->assertFalse($called);
    }

    /**
     * Return an attribute mock that provides array rules via getData('validate_rules').
     *
     * @param array $rules
     */
    private function mockAttributeRulesArray(array $rules): void
    {
        $attribute = $this->createAttributeMockForGetData('validate_rules', $rules);
        $this->eavConfig->method('getAttribute')->with('customer', 'dob')->willReturn($attribute);
        $this->json->expects($this->never())->method('unserialize');
    }

    /**
     * Create an attribute mock stubbing only getData($key).
     *
     * @param string $key
     * @param mixed $value
     * @return AbstractAttribute&MockObject
     */
    private function createAttributeMockForGetData(string $key, $value)
    {
        $attribute = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->getMockForAbstractClass();

        $attribute->method('getData')->with($key)->willReturn($value);
        return $attribute;
    }

    /**
     * @return void
     * @throws InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testInvalidJsonRulesCaughtAndIgnored(): void
    {
        $customer = $this->createCustomerMock('1990-06-15');

        $badJson = '{invalid json}';
        $attribute = $this->createAttributeMockForGetData('validate_rules', $badJson);
        $this->eavConfig->method('getAttribute')->with('customer', 'dob')->willReturn($attribute);

        $this->json
            ->expects($this->once())
            ->method('unserialize')
            ->with($badJson)
            ->willThrowException(new \InvalidArgumentException('bad json'));

        $result = $this->createMock(CustomerInterface::class);
        $called = false;
        $proceed = function (CustomerInterface $c, $hash = null) use (&$called, $result) {
            $called = true;
            return $result;
        };

        $actual = $this->plugin->aroundSave($this->repo, $proceed, $customer, null);

        $this->assertTrue($called, 'Proceed should be called when rules JSON is invalid (caught and ignored).');
        $this->assertSame($result, $actual);
    }

    /**
     * @return void
     * @throws InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testFallbackToGetValidateRulesArrayIsUsed(): void
    {
        $customer = $this->createCustomerMock('1979-12-31');

        $this->mockAttributeRulesViaGetValidateRules([
            'date_range_min' => '1980-01-01',
            'date_range_max' => '2000-12-31',
        ]);

        $called = false;
        $proceed = $this->proceedPlugin($called, $customer);

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('on or after 1980-01-01');
        $this->plugin->aroundSave($this->repo, $proceed, $customer, null);

        $this->assertFalse($called);
    }

    /**
     * Mock attribute so validate_rules is not an array/string, forcing fallback to getValidateRules().
     *
     * @param array $rules
     */
    private function mockAttributeRulesViaGetValidateRules(array $rules): void
    {
        $attribute = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->addMethods(['getValidateRules'])
            ->getMockForAbstractClass();

        // Force non-array rules to trigger the fallback
        $attribute->method('getData')->with('validate_rules')->willReturn(null);
        $attribute->method('getValidateRules')->willReturn($rules);

        $this->eavConfig->method('getAttribute')->with('customer', 'dob')->willReturn($attribute);
        $this->json->expects($this->never())->method('unserialize');
    }

    /**
     * @return void
     * @throws InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testDobZeroEpochInvalidThrows(): void
    {
        // Covers: return null; in parseAnyDate() for <= 0 timestamps
        $customer = $this->createCustomerMock(0);

        // Any rules; they won't be reached because DOB is invalid first
        $this->mockAttributeRulesArray([
            'date_range_min' => '1900-01-01',
            'date_range_max' => '2100-01-01',
        ]);

        $called = false;
        $proceed = $this->proceedPlugin($called, $customer);

        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Date of Birth is invalid.');
        $this->plugin->aroundSave($this->repo, $proceed, $customer, null);

        $this->assertFalse($called);
    }

    /**
     * Create a proceed closure that marks $called and returns either the given $result or the original $customer.
     *
     * @param bool $called Will be set to true when proceed is invoked
     * @param CustomerInterface|null $result Optional value to return instead of $customer
     * @return callable
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function proceedPlugin(bool &$called, ?CustomerInterface $result = null): callable
    {
        return function (CustomerInterface $c, $hash = null) use (&$called) {
            $called = true;
            return $c;
        };
    }

    /**
     * Create a customer mock with a specific DOB value.
     *
     * @param mixed $dob
     * @return CustomerInterface&MockObject
     */
    private function createCustomerMock($dob)
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getDob')->willReturn($dob);
        return $customer;
    }
}
