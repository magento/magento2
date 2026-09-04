<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Attribute\Data;

use Magento\Customer\Model\Attribute\Data\Postcode;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\StringUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class PostcodeTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var DirectoryHelper|MockObject
     */
    protected $directoryHelperMock;

    /**
     * @var AbstractAttribute|MockObject
     */
    protected $attributeMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $localeMock;

    /**
     * @var ResolverInterface|MockObject
     */
    private $localeResolverMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var StringUtils|MockObject
     */
    private $stringHelperMock;

    protected function setUp(): void
    {
        $this->localeMock = $this->createMock(TimezoneInterface::class);
        $this->localeResolverMock = $this->createMock(ResolverInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->directoryHelperMock = $this->createMock(\Magento\Directory\Helper\Data::class);
        $this->stringHelperMock = $this->createMock(StringUtils::class);
        $this->attributeMock = $this->createPartialMockWithReflection(
            AbstractAttribute::class,
            ['getStoreLabel', 'getValidateRules']
        );
    }

    /**
     * @param string $value to assign to boolean
     * @param bool $expected text output
     * @param string $countryId
     * @param bool $isOptional
     * */
    #[DataProvider('validateValueDataProvider')]
    public function testValidateValue($value, $expected, $countryId, $isOptional)
    {
        $storeLabel = 'Zip/Postal Code';
        $this->attributeMock->expects($this->any())
            ->method('getStoreLabel')
            ->willReturn($storeLabel);

        $this->directoryHelperMock->expects($this->once())
            ->method('isZipCodeOptional')
            ->willReturnMap([
                [$countryId, $isOptional],
            ]);

        $object = new Postcode(
            $this->localeMock,
            $this->loggerMock,
            $this->localeResolverMock,
            $this->directoryHelperMock,
            $this->stringHelperMock
        );
        $object->setAttribute($this->attributeMock);
        $object->setExtractedData(['country_id' => $countryId]);

        $actual = $object->validateValue($value);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public static function validateValueDataProvider()
    {
        return [
            ['', ['"Zip/Postal Code" is a required value.'], 'US', false],
            ['90034', true, 'US', false],
            ['', true, 'IE', true],
            ['90034', true, 'IE', true],
        ];
    }

    /**
     * Test validation of length and input rules
     *
     * @param string $value
     * @param bool|array $expected
     * @param array $validateRules
     * @param string $countryId
     * @param bool $isOptional
     * */
    #[DataProvider('validateValueWithRulesDataProvider')]
    public function testValidateValueWithRules(
        string $value,
        bool|array $expected,
        array $validateRules,
        string $countryId,
        bool $isOptional
    ) {
        $storeLabel = 'Zip/Postal Code';
        $this->attributeMock->expects($this->any())
            ->method('getStoreLabel')
            ->willReturn($storeLabel);

        $this->attributeMock->expects($this->any())
            ->method('getValidateRules')
            ->willReturn($validateRules);

        $this->directoryHelperMock->expects($this->once())
            ->method('isZipCodeOptional')
            ->willReturnMap([
                [$countryId, $isOptional],
            ]);

        if (!empty($validateRules['max_text_length'])) {
            $this->stringHelperMock->expects($this->any())
                ->method('strlen')
                ->willReturnCallback(function ($str) {
                    return strlen(trim($str));
                });
        }

        $object = new Postcode(
            $this->localeMock,
            $this->loggerMock,
            $this->localeResolverMock,
            $this->directoryHelperMock,
            $this->stringHelperMock
        );
        $object->setAttribute($this->attributeMock);
        $object->setExtractedData(['country_id' => $countryId]);

        $actual = $object->validateValue($value);

        if (is_array($expected)) {
            $this->assertIsArray($actual);
            $this->assertCount(count($expected), $actual);
            foreach ($expected as $key => $expectedMessage) {
                $actualMessage = $actual[$key];
                // Convert Phrase to string if needed
                if ($actualMessage instanceof \Magento\Framework\Phrase) {
                    $actualMessage = $actualMessage->__toString();
                }
                $this->assertStringContainsString($expectedMessage, $actualMessage);
            }
        } else {
            $this->assertEquals($expected, $actual);
        }
    }

    /**
     * @return array
     */
    public static function validateValueWithRulesDataProvider()
    {
        return [
            // Test min length validation
            [
                '12',
                ['"Zip/Postal Code" length must be equal or greater than 5 characters.'],
                ['input_validation' => 'alphanumeric', 'min_text_length' => 5],
                'US',
                false
            ],
            // Test max length validation
            [
                '1234567890',
                ['"Zip/Postal Code" length must be equal or less than 6 characters.'],
                ['input_validation' => 'alphanumeric', 'max_text_length' => 6],
                'US',
                false
            ],
            // Test valid length
            [
                '12345',
                true,
                ['input_validation' => 'alphanumeric', 'min_text_length' => 5, 'max_text_length' => 6],
                'US',
                false
            ],
            // Test no validation rules
            [
                '90034',
                true,
                [],
                'US',
                false
            ],
        ];
    }
}
