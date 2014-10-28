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
 * Test class for \Magento\Eav\Model\Entity\Attribute\Set
 */
namespace Magento\Eav\Model\Entity\Attribute;

class SetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Set
     */
    protected $_model;

    protected function setUp()
    {
        $resource = $this->getMock('Magento\Eav\Model\Resource\Entity\Attribute\Set', array(), array(), '', false);
        $attrGroupFactory = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\GroupFactory',
            array(),
            array(),
            '',
            false,
            false
        );
        $attrFactory = $this->getMock('Magento\Eav\Model\Entity\AttributeFactory', array(), array(), '', false, false);
        $arguments = array(
            'attrGroupFactory' => $attrGroupFactory,
            'attributeFactory' => $attrFactory,
            'resource' => $resource
        );
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject('Magento\Eav\Model\Entity\Attribute\Set', $arguments);
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    /**
     * @param string $attributeSetName
     * @param string $exceptionMessage
     * @dataProvider invalidAttributeSetDataProvider
     */
    public function testValidateWithExistingName($attributeSetName, $exceptionMessage)
    {
        $this->_model->getResource()->expects($this->any())->method('validate')->will($this->returnValue(false));

        $this->setExpectedException('Magento\Eav\Exception', $exceptionMessage);
        $this->_model->setAttributeSetName($attributeSetName);
        $this->_model->validate();
    }

    public function testValidateWithNonexistentValidName()
    {
        $this->_model->getResource()->expects($this->any())->method('validate')->will($this->returnValue(true));

        $this->_model->setAttributeSetName('nonexistent_name');
        $this->assertTrue($this->_model->validate());
    }

    /**
     * Retrieve data for invalid
     *
     * @return array
     */
    public function invalidAttributeSetDataProvider()
    {
        return array(
            array('', 'Attribute set name is empty.'),
            array('existing_name', 'An attribute set with the "existing_name" name already exists.')
        );
    }
}
