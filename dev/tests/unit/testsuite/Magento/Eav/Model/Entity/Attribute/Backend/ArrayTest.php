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
namespace Magento\Eav\Model\Entity\Attribute\Backend;

class ArrayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend
     */
    protected $_model;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    protected $_attribute;

    protected function setUp()
    {
        $this->_attribute = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute',
            array('getAttributeCode', '__wakeup'),
            array(),
            '',
            false
        );
        $logger = $this->getMock('Magento\Framework\Logger', array(), array(), '', false);
        $this->_model = new \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend($logger);
        $this->_model->setAttribute($this->_attribute);
    }

    /**
     * @dataProvider attributeValueDataProvider
     */
    public function testValidate($data)
    {
        $this->_attribute->expects($this->atLeastOnce())->method('getAttributeCode')->will($this->returnValue('code'));
        $product = new \Magento\Framework\Object(array('code' => $data));
        $this->_model->validate($product);
        $this->assertEquals('1,2,3', $product->getCode());
    }

    public static function attributeValueDataProvider()
    {
        return array(array(array(1, 2, 3)), array('1,2,3'));
    }
}
