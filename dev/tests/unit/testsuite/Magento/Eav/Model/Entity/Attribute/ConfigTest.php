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

/**
 * Test class for \Magento\Eav\Model\Entity\Attribute\Config
 */
namespace Magento\Eav\Model\Entity\Attribute;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Config
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheId;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_attribute;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_entityType;

    protected function setUp()
    {
        $this->_attribute = $this->getMock('Magento\Eav\Model\Entity\Attribute', array(), array(), '', false);
        $this->_entityType = $this->getMock('Magento\Eav\Model\Entity\Type', array(), array(), '', false);
        $this->_readerMock = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\Config\Reader',
            array(),
            array(),
            '',
            false
        );
        $this->_cacheMock = $this->getMock('Magento\Framework\App\Cache\Type\Config', array(), array(), '', false);
        $this->_cacheId = 'eav_attributes';
        $this->_cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            $this->equalTo($this->_cacheId)
        )->will(
            $this->returnValue(serialize(array()))
        );

        $this->_model = new \Magento\Eav\Model\Entity\Attribute\Config(
            $this->_readerMock,
            $this->_cacheMock,
            $this->_cacheId
        );
    }

    public function testGetLockedFieldsEmpty()
    {
        $this->_entityType->expects($this->once())->method('getEntityTypeCode')->will($this->returnValue('test_code'));
        $this->_attribute->expects(
            $this->once()
        )->method(
            'getEntityType'
        )->will(
            $this->returnValue($this->_entityType)
        );

        $this->_attribute->expects(
            $this->once()
        )->method(
            'getAttributeCode'
        )->will(
            $this->returnValue('attribute_code')
        );
        $result = $this->_model->getLockedFields($this->_attribute);
        $this->assertEquals(array(), $result);
    }

    public function testGetLockedFields()
    {
        $this->_entityType->expects(
            $this->once()
        )->method(
            'getEntityTypeCode'
        )->will(
            $this->returnValue('test_code1/test_code2')
        );
        $this->_attribute->expects(
            $this->once()
        )->method(
            'getEntityType'
        )->will(
            $this->returnValue($this->_entityType)
        );

        $this->_attribute->expects($this->once())->method('getAttributeCode')->will($this->returnValue('test_code'));
        $data = array(
            'test_code1' => array(
                'test_code2' => array('attributes' => array('test_code' => array('test_code1' => 'test_code1')))
            )
        );
        $this->_model->merge($data);
        $result = $this->_model->getLockedFields($this->_attribute);
        $this->assertEquals(array('test_code1' => 'test_code1'), $result);
    }

    public function testGetEntityAttributesLockedFields()
    {
        $data = array(
            'entity_code' => array(
                'attributes' => array(
                    'attribute_code' => array(
                        'attribute_data' => array('locked' => 'locked_field', 'code' => 'code_test')
                    )
                )
            )
        );
        $this->_model->merge($data);
        $result = $this->_model->getEntityAttributesLockedFields('entity_code');
        $this->assertEquals(array('attribute_code' => array('code_test')), $result);
    }
}
