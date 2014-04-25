<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Checkout\Model\Agreements;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

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
            'Magento\Checkout\Model\Agreements\AgreementsValidator',
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
            [[1, '4', 3,], true],
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
        $provider = $this->getMockForAbstractClass('Magento\Checkout\Model\Agreements\AgreementsProviderInterface');
        $provider->expects($this->once())
            ->method('getRequiredAgreementIds')
            ->will($this->returnValue([1, 3, '4']));

        $this->object = $this->objectManagerHelper->getObject(
            'Magento\Checkout\Model\Agreements\AgreementsValidator',
            ['list' => [$provider]]
        );
        $this->assertEquals($result, $this->object->isValid($data));
    }
}