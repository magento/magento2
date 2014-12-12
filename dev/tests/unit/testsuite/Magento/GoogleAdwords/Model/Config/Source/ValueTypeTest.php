<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GoogleAdwords\Model\Config\Source;

class ValueTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GoogleAdwords\Model\Config\Source\ValueType
     */
    protected $_model;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $objectManager->getObject('Magento\GoogleAdwords\Model\Config\Source\ValueType', []);
    }

    public function testToOptionArray()
    {
        $this->assertEquals(
            [
                [
                    'value' => \Magento\GoogleAdwords\Helper\Data::CONVERSION_VALUE_TYPE_DYNAMIC,
                    'label' => 'Dynamic',
                ],
                [
                    'value' => \Magento\GoogleAdwords\Helper\Data::CONVERSION_VALUE_TYPE_CONSTANT,
                    'label' => 'Constant'
                ],
            ],
            $this->_model->toOptionArray()
        );
    }
}
