<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CardinalCommerce\Test\Unit\Model\Response;

use Magento\CardinalCommerce\Model\Response\JwtPayloadValidator;
use Magento\Framework\Intl\DateTimeFactory;
use PHPUnit\Framework\TestCase;

class JwtPayloadValidatorTest extends TestCase
{
    /**
     * @var JwtPayloadValidator
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->model = new JwtPayloadValidator(new DateTimeFactory());
    }

    /**
     * Tests successful cases.
     *
     * @param array $token
     * @dataProvider validateSuccessDataProvider
     */
    public function testValidateSuccess(array $token)
    {
        $this->assertTrue(
            $this->model->validate($token)
        );
    }

    /**
     * @case 1. All data are correct, the transaction was successful (Visa, AMEX)
     * @case 2. All data are correct, the transaction was successful but requires in no additional action (Visa, AMEX)
     * @case 3. All data are correct, the transaction was successful (MasterCard)
     * @case 4. All data are correct, the transaction was successful but requires in no additional action (MasterCard)
     *
     * @return array
     */
    public static function validateSuccessDataProvider()
    {
        $expTimestamp = self::getValidExpTimestamp();

        return [
            1 => self::createToken('05', '0', 'SUCCESS', $expTimestamp),
            2 => self::createToken('06', '0', 'NOACTION', $expTimestamp),
            3 => self::createToken('02', '0', 'SUCCESS', $expTimestamp),
            4 => self::createToken('01', '0', 'NOACTION', $expTimestamp),
        ];
    }

    /**
     * Case when 3DS authentication is either failed or could not be attempted.
     *
     * @param array $token
     * @dataProvider validationEciFailsDataProvider
     */
    public function testValidationEciFails(array $token)
    {
        $this->assertFalse(
            $this->model->validate($token),
            'Negative ECIFlag value validation fails'
        );
    }

    /**
     * ECIFlag value when 3DS authentication is either failed or could not be attempted.
     *
     * @case 1. Visa, AMEX, JCB
     * @case 2. MasterCard
     * @return array
     */
    public static function validationEciFailsDataProvider(): array
    {
        $expTimestamp = self::getValidExpTimestamp();
        return [
            1 => self::createToken('07', '0', 'NOACTION', $expTimestamp),
            2 => self::createToken('00', '0', 'NOACTION', $expTimestamp),
        ];
    }

    /**
     * Case when resulting state of the transaction is negative.
     *
     * @param array $token
     * @dataProvider validationActionCodeFailsDataProvider
     */
    public function testValidationActionCodeFails(array $token)
    {
        $this->assertFalse(
            $this->model->validate($token),
            'Negative ActionCode value validation fails'
        );
    }

    /**
     * ECIFlag value when 3DS authentication is either failed or could not be attempted.
     *
     * @return array
     */
    public static function validationActionCodeFailsDataProvider(): array
    {
        $expTimestamp = self::getValidExpTimestamp();
        return [
            self::createToken('05', '0', 'FAILURE', $expTimestamp),
            self::createToken('05', '0', 'ERROR', $expTimestamp),
        ];
    }

    /**
     * Case when ErrorNumber not equal 0.
     */
    public function testValidationErrorNumberFails()
    {
        $notAllowedErrorNumber = '10';
        $expTimestamp = $this->getValidExpTimestamp();
        $token =  $this->createToken('05', $notAllowedErrorNumber, 'SUCCESS', $expTimestamp);
        $this->assertFalse(
            $this->model->validate($token),
            'Negative ErrorNumber value validation fails'
        );
    }

    /**
     * Case when ErrorNumber not equal 0.
     */
    public function testValidationExpirationFails()
    {
        $expTimestamp = $this->getOutdatedExpTimestamp();
        $token =  $this->createToken('05', '0', 'SUCCESS', $expTimestamp);
        $this->assertFalse(
            $this->model->validate($token),
            'Expiration date validation fails'
        );
    }

    /**
     * Creates a token.
     *
     * @param string $eciFlag
     * @param string $errorNumber
     * @param string $actionCode
     * @param int $expTimestamp
     *
     * @return array
     */
    private static function createToken(string $eciFlag, string $errorNumber, string $actionCode, int $expTimestamp): array // @codingStandardsIgnoreLine
    {
        return [
            [
                'Payload' => [
                    'Payment' => [
                        'ExtendedData' => [
                            'ECIFlag' => $eciFlag,
                        ],
                    ],
                    'ActionCode' => $actionCode,
                    'ErrorNumber' => $errorNumber
                ],
                'exp' => $expTimestamp
            ]
        ];
    }

    /**
     * Returns valid expiration timestamp.
     *
     * @return int
     */
    private static function getValidExpTimestamp()
    {
        $dateTimeFactory = new DateTimeFactory();
        $currentDate = $dateTimeFactory->create('now', new \DateTimeZone('UTC'));

        return $currentDate->getTimestamp() + 3600;
    }

    /**
     * Returns outdated expiration timestamp.
     *
     * @return int
     */
    private function getOutdatedExpTimestamp()
    {
        $dateTimeFactory = new DateTimeFactory();
        $currentDate = $dateTimeFactory->create('now', new \DateTimeZone('UTC'));

        return $currentDate->getTimestamp() - 3600;
    }
}
