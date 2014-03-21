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
namespace Magento\Usps\Model\Source;

class GenericTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Usps\Model\Source\Generic
     */
    protected $_generic;

    /**
     * @var \Magento\Usps\Model\Carrier
     */
    protected $_uspsModel;

    public function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_uspsModel = $this->getMockBuilder(
            'Magento\Usps\Model\Carrier'
        )->setMethods(
            array('getCode')
        )->disableOriginalConstructor()->getMock();

        $this->_generic = $helper->getObject(
            '\Magento\Usps\Model\Source\Generic',
            array('shippingUsps' => $this->_uspsModel)
        );
    }

    /**
     * @dataProvider getCodeDataProvider
     * @param array$expected array
     * @param array $options
     */
    public function testToOptionArray($expected, $options)
    {
        $this->_uspsModel->expects($this->any())->method('getCode')->will($this->returnValue($options));

        $this->assertEquals($expected, $this->_generic->toOptionArray());
    }

    /**
     * @return array expected result and return of \Magento\Usps\Model\Carrier::getCode
     */
    public function getCodeDataProvider()
    {
        return array(
            array(array(array('value' => 'Val', 'label' => 'Label')), array('Val' => 'Label')),
            array(array(), false)
        );
    }
}
