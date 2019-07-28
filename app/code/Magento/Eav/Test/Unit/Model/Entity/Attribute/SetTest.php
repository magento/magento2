<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Eav\Model\Entity\Attribute\Set
 */
namespace Magento\Eav\Test\Unit\Model\Entity\Attribute;

class SetTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Set
     */
    protected $_model;

    protected function setUp()
    {
        $resource = $this->createMock(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set::class);
        $attrGroupFactory = $this->createMock(\Magento\Eav\Model\Entity\Attribute\GroupFactory::class);
        $attrFactory = $this->createMock(\Magento\Eav\Model\Entity\AttributeFactory::class);
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

        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage($exceptionMessage);
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
            ['', 'The attribute set name is empty. Enter the name and try again.'],
            ['existing_name', 'A "existing_name" attribute set name already exists. Create a new name and try again.']
        ];
    }
}
