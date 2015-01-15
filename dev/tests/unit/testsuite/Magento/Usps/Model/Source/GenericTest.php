<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
            ['getCode']
        )->disableOriginalConstructor()->getMock();

        $this->_generic = $helper->getObject(
            '\Magento\Usps\Model\Source\Generic',
            ['shippingUsps' => $this->_uspsModel]
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
        return [
            [[['value' => 'Val', 'label' => 'Label']], ['Val' => 'Label']],
            [[], false]
        ];
    }
}
