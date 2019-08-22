<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Unit\Model;

use Magento\CheckoutAgreements\Model\AgreementsValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class AgreementsValidatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var AgreementsValidator */
    protected $object;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp()
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
     * @dataProvider isValidDataProvider
     * @param $data
     * @param $result
     */
    public function testIsValid($data, $result)
    {
        $this->object = $this->objectManagerHelper->getObject(
            \Magento\CheckoutAgreements\Model\AgreementsValidator::class,
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
     * @dataProvider notIsValidDataProvider
     * @param $data
     * @param $result
     */
    public function testNotIsValid($data, $result)
    {
        $provider = $this->getMockForAbstractClass(
            \Magento\CheckoutAgreements\Model\AgreementsProviderInterface::class
        );
        $provider->expects($this->once())
            ->method('getRequiredAgreementIds')
            ->will($this->returnValue([1, 3, '4']));

        $this->object = $this->objectManagerHelper->getObject(
            \Magento\CheckoutAgreements\Model\AgreementsValidator::class,
            ['list' => [$provider]]
        );
        $this->assertEquals($result, $this->object->isValid($data));
    }
}
