<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Unit\Model;

use Magento\CheckoutAgreements\Model\AgreementsValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class AgreementsValidatorTest extends \PHPUnit_Framework_TestCase
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
            'Magento\CheckoutAgreements\Model\AgreementsValidator',
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
        $provider = $this->getMockForAbstractClass('\Magento\CheckoutAgreements\Model\AgreementsProviderInterface');
        $provider->expects($this->once())
            ->method('getRequiredAgreementIds')
            ->will($this->returnValue([1, 3, '4']));

        $this->object = $this->objectManagerHelper->getObject(
            'Magento\CheckoutAgreements\Model\AgreementsValidator',
            ['list' => [$provider]]
        );
        $this->assertEquals($result, $this->object->isValid($data));
    }
}
