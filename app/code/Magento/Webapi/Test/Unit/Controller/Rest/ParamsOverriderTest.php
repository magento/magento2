<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Controller\Rest;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Webapi\Controller\Rest\ParamOverriderCustomerId;
use Magento\Webapi\Controller\Rest\ParamsOverrider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test Magento\Webapi\Controller\Rest\ParamsOverrider
 */
class ParamsOverriderTest extends TestCase
{
    /**
     * @param array $requestData Data from the request
     * @param array $parameters Data from config about which parameters to override
     * @param array $expectedOverriddenParams Result of overriding $requestData when applying rules from $parameters
     * @param int $userId The id of the user invoking the request
     * @param int $userType The type of user invoking the request
     */
    #[DataProvider('overrideParamsDataProvider')]
    public function testOverrideParams($requestData, $parameters, $expectedOverriddenParams, $userId, $userType)
    {
        $objectManager = new ObjectManager($this);

        $userContextMock = $this->createPartialMock(
            UserContextInterface::class,
            ['getUserId', 'getUserType']
        );
        $userContextMock->expects($this->any())->method('getUserId')->willReturn($userId);
        $userContextMock->expects($this->any())->method('getUserType')->willReturn($userType);

        $paramOverriderCustomerId = $objectManager->getObject(
            ParamOverriderCustomerId::class,
            ['userContext' => $userContextMock]
        );

        /** @var MockObject $objectConverter */
        $objectConverter = $this->createPartialMock(
            SimpleDataObjectConverter::class,
            ['convertKeysToCamelCase']
        );
        $objectConverter->expects($this->any())
            ->method('convertKeysToCamelCase')
            ->willReturnCallback(
                function (array $array) {
                    $converted = [];
                    foreach ($array as $key => $value) {
                        $converted[mb_strtolower($key)] = $value;
                    }

                    return $converted;
                }
            );

        /** @var ParamsOverrider $paramsOverrider */
        $paramsOverrider = $objectManager->getObject(
            ParamsOverrider::class,
            [
                'paramOverriders' => ['%customer_id%' => $paramOverriderCustomerId ],
                'dataObjectConverter' => $objectConverter
            ]
        );

        $this->assertEquals($expectedOverriddenParams, $paramsOverrider->override($requestData, $parameters));
    }

    /**
     * @return array
     */
    public static function overrideParamsDataProvider()
    {
        return [
            'force false, value present' => [
                ['Name1' => 'valueIn'],
                ['Name1' => ['force' => false, 'value' => 'valueOverride']],
                ['Name1' => 'valueIn'],
                1,
                UserContextInterface::USER_TYPE_INTEGRATION,
            ],
            'force true, value present' => [
                ['Name1' => 'valueIn'],
                ['Name1' => ['force' => true, 'value' => 'valueOverride']],
                ['Name1' => 'valueOverride'],
                1,
                UserContextInterface::USER_TYPE_INTEGRATION,
            ],
            'force true, value not present' => [
                ['Name1' => 'valueIn'],
                ['Name2' => ['force' => true, 'value' => 'valueOverride']],
                ['Name1' => 'valueIn', 'Name2' => 'valueOverride'],
                1,
                UserContextInterface::USER_TYPE_INTEGRATION,
            ],
            'force false, value not present' => [
                ['Name1' => 'valueIn'],
                ['Name2' => ['force' => false, 'value' => 'valueOverride']],
                ['Name1' => 'valueIn', 'Name2' => 'valueOverride'],
                1,
                UserContextInterface::USER_TYPE_INTEGRATION,
            ],
            'force true, value present, override value is %customer_id%' => [
                ['Name1' => 'valueIn'],
                ['Name1' => ['force' => true, 'value' => '%customer_id%']],
                ['Name1' => '1234'],
                1234,
                UserContextInterface::USER_TYPE_CUSTOMER,
            ],
            'force true, value present, override value is %customer_id%, not a customer' => [
                ['Name1' => 'valueIn'],
                ['Name1' => ['force' => true, 'value' => '%customer_id%']],
                ['Name1' => null],
                1234,
                UserContextInterface::USER_TYPE_INTEGRATION,
            ],
        ];
    }

    #[DataProvider('nestedOverrideDataProvider')]
    public function testOverrideNestedParams(array $requestData, array $parameters, array $expected): void
    {
        $this->assertSame($expected, $this->createNestedParamsOverrider()->override($requestData, $parameters));
    }

    public static function nestedOverrideDataProvider(): array
    {
        $forcedQuoteId = ['cartItem.quote_id' => ['force' => true, 'value' => '42']];
        return [
            'forced value replaces snake_case key' => [
                ['cartItem' => ['sku' => 'simple', 'quote_id' => '7']],
                $forcedQuoteId,
                ['cartItem' => ['sku' => 'simple', 'quote_id' => '42']],
            ],
            'forced value replaces camelCase key' => [
                ['cartItem' => ['sku' => 'simple', 'quoteId' => '7']],
                $forcedQuoteId,
                ['cartItem' => ['sku' => 'simple', 'quoteId' => '42']],
            ],
            'forced value added when key missing' => [
                ['cartItem' => ['sku' => 'simple']],
                $forcedQuoteId,
                ['cartItem' => ['sku' => 'simple', 'quote_id' => '42']],
            ],
            'forced value creates missing parent' => [
                [],
                $forcedQuoteId,
                ['cartItem' => ['quote_id' => '42']],
            ],
            'null parent replaced with array' => [
                ['cartItem' => null],
                $forcedQuoteId,
                ['cartItem' => ['quote_id' => '42']],
            ],
            'snake_case parent key in input' => [
                ['cart_item' => ['sku' => 'simple']],
                ['cart_item.quote_id' => ['force' => true, 'value' => '42']],
                ['cart_item' => ['sku' => 'simple', 'quote_id' => '42']],
            ],
            'deeper path' => [
                ['cartItem' => ['productOption' => ['sku' => 'simple']]],
                ['cartItem.product_option.quote_id' => ['force' => true, 'value' => '42']],
                ['cartItem' => ['productOption' => ['sku' => 'simple', 'quote_id' => '42']]],
            ],
            'not forced value kept when set in nested array' => [
                ['cartItem' => ['quote_id' => '7']],
                ['cartItem.quote_id' => ['force' => false, 'value' => '42']],
                ['cartItem' => ['quote_id' => '7']],
            ],
        ];
    }

    #[DataProvider('malformedNestedPayloadDataProvider')]
    public function testOverrideRejectsNonArrayIntermediateValue(
        array $requestData,
        array $parameters,
        string $expectedMessage
    ): void {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->createNestedParamsOverrider()->override($requestData, $parameters);
    }

    public static function malformedNestedPayloadDataProvider(): array
    {
        $forcedQuoteId = ['cartItem.quote_id' => ['force' => true, 'value' => '42']];
        return [
            'string parent, forced' => [
                ['cartItem' => 'invalid'],
                $forcedQuoteId,
                'Invalid value of "invalid" provided for the cartItem field.',
            ],
            'integer parent, forced' => [
                ['cartItem' => 5],
                $forcedQuoteId,
                'Invalid value of "5" provided for the cartItem field.',
            ],
            'string parent, not forced' => [
                ['cartItem' => 'invalid'],
                ['cartItem.quote_id' => ['force' => false, 'value' => '42']],
                'Invalid value of "invalid" provided for the cartItem field.',
            ],
            'snake_case string parent' => [
                ['cart_item' => 'invalid'],
                ['cart_item.quote_id' => ['force' => true, 'value' => '42']],
                'Invalid value of "invalid" provided for the cart_item field.',
            ],
            'string parent on deeper path' => [
                ['cartItem' => 'invalid'],
                ['cartItem.nested.quote_id' => ['force' => true, 'value' => '42']],
                'Invalid value of "invalid" provided for the cartItem field.',
            ],
            'string intermediate on deeper path' => [
                ['cartItem' => ['nested' => 'invalid']],
                ['cartItem.nested.quote_id' => ['force' => true, 'value' => '42']],
                'Invalid value of "invalid" provided for the cartItem.nested field.',
            ],
        ];
    }

    private function createNestedParamsOverrider(): ParamsOverrider
    {
        return new ParamsOverrider(
            [],
            new SimpleDataObjectConverter($this->createStub(DataObjectProcessor::class))
        );
    }
}
