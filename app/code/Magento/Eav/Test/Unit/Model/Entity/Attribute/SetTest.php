<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Eav\Model\Entity\Attribute\Set
 */
namespace Magento\Eav\Test\Unit\Model\Entity\Attribute;

use Magento\Eav\Model\Entity\Attribute\GroupFactory;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class SetTest extends TestCase
{
    /**
     * @var Set
     */
    protected $_model;

    protected function setUp(): void
    {
        $resource = $this->createMock(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set::class);
        $attrGroupFactory = $this->createMock(GroupFactory::class);
        $attrFactory = $this->createMock(AttributeFactory::class);
        $arguments = [
            'attrGroupFactory' => $attrGroupFactory,
            'attributeFactory' => $attrFactory,
            'resource' => $resource,
        ];
        $objectManagerHelper = new ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(Set::class, $arguments);
    }

    protected function tearDown(): void
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
        $this->_model->getResource()->expects($this->any())->method('validate')->willReturn(false);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage($exceptionMessage);
        $this->_model->setAttributeSetName($attributeSetName);
        $this->_model->validate();
    }

    public function testValidateWithNonexistentValidName()
    {
        $this->_model->getResource()->expects($this->any())->method('validate')->willReturn(true);

        $this->_model->setAttributeSetName('nonexistent_name');
        $this->assertTrue($this->_model->validate());
    }

    /**
     * Retrieve data for invalid
     *
     * @return array
     */
    public static function invalidAttributeSetDataProvider()
    {
        return [
            ['', 'The attribute set name is empty. Enter the name and try again.'],
            ['existing_name', 'A "existing_name" attribute set name already exists. Create a new name and try again.']
        ];
    }
}
