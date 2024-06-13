<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CheckoutAgreements\Test\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\CheckoutAgreements\Model\AgreementsProviderInterface;
use Magento\CheckoutAgreements\Model\AgreementsValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

class AgreementsValidatorTest extends TestCase
{
    /** @var AgreementsValidator */
    protected $object;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    /**
     * @return array
     */
    public static function isValidDataProvider()
    {
        return [
            [[], true],
            [[1], true]
        ];
    }

    /**
     * @param $data
     * @param $result
     */
    #[DataProvider('isValidDataProvider')]
    public function testIsValid($data, $result)
    {
        $this->object = $this->objectManagerHelper->getObject(
            AgreementsValidator::class,
            []
        );
        $this->assertEquals($result, $this->object->isValid($data));
    }

    /**
     * @return array
     */
    public static function notIsValidDataProvider()
    {
        return [
            [[1, 3, '4'], true],
            [[1, '4', 3], true],
            [[1, 3, 4], true],
            [[1, 3, 4, 5], true],
            [[], false],
            [[1], false],
        ];
    }

    /**
     * @param $data
     * @param $result
     */
    #[DataProvider('notIsValidDataProvider')]
    public function testNotIsValid($data, $result)
    {
        $provider = $this->createMock(AgreementsProviderInterface::class);
        $provider->expects($this->once())
            ->method('getRequiredAgreementIds')
            ->willReturn([1, 3, '4']);

        $this->object = $this->objectManagerHelper->getObject(
            AgreementsValidator::class,
            ['list' => [$provider]]
        );
        $this->assertEquals($result, $this->object->isValid($data));
    }
}
