<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Eav\Model\Entity\Attribute\Set
 */
namespace Magento\Eav\Test\Unit\Model\Entity\Attribute;

class SetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Set
     */
    protected $_model;

    protected function setUp()
    {
        $resource = $this->getMock(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set::class, [], [], '', false);
        $attrGroupFactory = $this->getMock(
            \Magento\Eav\Model\Entity\Attribute\GroupFactory::class,
            [],
            [],
            '',
            false,
            false
        );
        $attrFactory = $this->getMock(\Magento\Eav\Model\Entity\AttributeFactory::class, [], [], '', false, false);
        $arguments = [
            'attrGroupFactory' => $attrGroupFactory,
            'attributeFactory' => $attrFactory,
            'resource' => $resource,
        ];
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(\Magento\Eav\Model\Entity\Attribute\Set::class, $arguments);
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

        $this->setExpectedException(\Magento\Framework\Exception\LocalizedException::class, $exceptionMessage);
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
        return [
            ['', 'Attribute set name is empty.'],
            ['existing_name', 'An attribute set named "existing_name" already exists.']
        ];
    }
}
