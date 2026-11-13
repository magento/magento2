<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Usps\Test\Unit\Model;

use Magento\Usps\Model\ShippingMethodManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ShippingMethodManagerTest extends TestCase
{
    private const PRIORITY_MAIL = 'PRIORITY_MAIL_MACHINABLE_SINGLE-PIECE';
    private const PRIORITY_MAIL_FLAT_RATE_ENVELOPE = 'PRIORITY_MAIL_FLAT_RATE_ENVELOPE';
    private const PRIORITY_MAIL_INTERNATIONAL = 'PRIORITY_MAIL_INTERNATIONAL_ISC_SINGLE-PIECE';
    private const MEDIA_MAIL = 'MEDIA_MAIL_MACHINABLE_5-DIGIT';

    /**
     * @var ShippingMethodManager
     */
    private $shippingMethodManager;

    protected function setUp(): void
    {
        $this->shippingMethodManager = new ShippingMethodManager();
    }

    /**
     * @param array $rate
     * @param array $allowedMethodCodes
     * @param string|null $expectedMethodCode
     * @return void
     */
    #[DataProvider('findAllowedMethodForRateDataProvider')]
    public function testFindAllowedMethodForRate(
        array $rate,
        array $allowedMethodCodes,
        ?string $expectedMethodCode
    ): void {
        $this->assertSame(
            $expectedMethodCode,
            $this->shippingMethodManager->findAllowedMethodForRate($rate, $allowedMethodCodes)
        );
    }

    /**
     * @return array
     */
    public static function findAllowedMethodForRateDataProvider(): array
    {
        return [
            'nonstandard single-piece prices the single-piece method' => [
                self::rate('PRIORITY_MAIL', 'SP', 'NONE'),
                [self::PRIORITY_MAIL],
                self::PRIORITY_MAIL,
            ],
            'flat rate envelope does not price the single-piece method' => [
                self::rate('PRIORITY_MAIL', 'FE', 'NONE'),
                [self::PRIORITY_MAIL],
                null,
            ],
            'flat rate envelope prices its own method when allowed after the single-piece one' => [
                self::rate('PRIORITY_MAIL', 'FE', 'NONE'),
                [self::PRIORITY_MAIL, self::PRIORITY_MAIL_FLAT_RATE_ENVELOPE],
                self::PRIORITY_MAIL_FLAT_RATE_ENVELOPE,
            ],
            'single-piece does not price a flat rate method listed first' => [
                self::rate('PRIORITY_MAIL', 'SP', 'NONE'),
                [self::PRIORITY_MAIL_FLAT_RATE_ENVELOPE, self::PRIORITY_MAIL],
                self::PRIORITY_MAIL,
            ],
            'single-piece does not price a flat rate method alone' => [
                self::rate('PRIORITY_MAIL', 'SP', 'NONE'),
                [self::PRIORITY_MAIL_FLAT_RATE_ENVELOPE],
                null,
            ],
            'destination-entry single-piece does not price the single-piece method' => [
                self::rate('PRIORITY_MAIL', 'SP', 'DESTINATION_DELIVERY_UNIT'),
                [self::PRIORITY_MAIL],
                null,
            ],
            'tray box does not price the single-piece method' => [
                self::rate('PRIORITY_MAIL', 'O2', 'AREA_DISTRIBUTION_CENTER'),
                [self::PRIORITY_MAIL],
                null,
            ],
            'cubic tier does not price the single-piece method' => [
                self::rate('PRIORITY_MAIL', 'CP', 'NONE'),
                [self::PRIORITY_MAIL],
                null,
            ],
            'international single-piece prices the international method' => [
                self::rate('PRIORITY_MAIL_INTERNATIONAL', 'SP', 'INTERNATIONAL_SERVICE_CENTER'),
                [self::PRIORITY_MAIL_INTERNATIONAL],
                self::PRIORITY_MAIL_INTERNATIONAL,
            ],
            'international flat rate envelope does not price the international single-piece method' => [
                self::rate('PRIORITY_MAIL_INTERNATIONAL', 'FE', 'INTERNATIONAL_SERVICE_CENTER'),
                [self::PRIORITY_MAIL_INTERNATIONAL],
                null,
            ],
            'media mail 5-digit variant prices the media mail method' => [
                self::rate('MEDIA_MAIL', '5D', 'NONE'),
                [self::MEDIA_MAIL],
                self::MEDIA_MAIL,
            ],
            'media mail single-piece prices the only media mail method' => [
                self::rate('MEDIA_MAIL', 'SP', 'NONE'),
                [self::MEDIA_MAIL],
                self::MEDIA_MAIL,
            ],
            'media mail nonstandard basic prices the only media mail method' => [
                self::rate('MEDIA_MAIL', 'BA', 'NONE'),
                [self::MEDIA_MAIL],
                self::MEDIA_MAIL,
            ],
            'media mail destination-entry variant does not price the media mail method' => [
                self::rate('MEDIA_MAIL', '5D', 'DESTINATION_DELIVERY_UNIT'),
                [self::MEDIA_MAIL],
                null,
            ],
            'other mail class is not matched' => [
                self::rate('PARCEL_SELECT', 'SP', 'NONE'),
                [self::PRIORITY_MAIL],
                null,
            ],
            'missing mail class is not matched' => [
                ['rateIndicator' => 'SP'],
                [self::PRIORITY_MAIL],
                null,
            ],
        ];
    }

    /**
     * @param string $mailClass
     * @param string $rateIndicator
     * @param string $destinationEntryFacilityType
     * @return array
     */
    private static function rate(string $mailClass, string $rateIndicator, string $destinationEntryFacilityType): array
    {
        return [
            'mailClass' => $mailClass,
            'rateIndicator' => $rateIndicator,
            'destinationEntryFacilityType' => $destinationEntryFacilityType,
        ];
    }
}
