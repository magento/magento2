<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker;

class AdvancedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Adminhtml\Product\Attribute\Grid
     */
    protected $block;

    /**
     * @var \Magento\Framework\Data\FormFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formFactory;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDate;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $yesNo;

    /**
     * @var \Magento\Eav\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavData;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * @var PropertyLocker|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $propertyLocker;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->registry = $this->getMock('\Magento\Framework\Registry');
        $this->formFactory = $this->getMock('Magento\Framework\Data\FormFactory', [], [], '', false);
        $this->yesNo = $this->getMock('Magento\Config\Model\Config\Source\Yesno');
        $this->localeDate = $this->getMock('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $this->eavData = $this->getMock('Magento\Eav\Helper\Data', [], [], '', false);
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->propertyLocker = $this->getMock(PropertyLocker::class, [], [], '', false);

        $this->block = $objectManager->getObject(
            'Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Advanced',
            [
                'registry' => $this->registry,
                'formFactory' => $this->formFactory,
                'localeDate' => $this->localeDate,
                'yesNo' => $this->yesNo,
                'eavData' => $this->eavData,
                'filesystem' => $this->filesystem,
                'propertyLocker' => $this->propertyLocker,
            ]
        );
    }

    public function testToHtml()
    {
        $fieldSet = $this->getMock('Magento\Framework\Data\Form\Element\Fieldset', [], [], '', false);
        $form = $this->getMock('Magento\Framework\Data\Form', [], [], '', false);
        $attributeModel = $this->getMock('\Magento\Catalog\Model\ResourceModel\Eav\Attribute', [], [], '', false);
        $entityType = $this->getMock('Magento\Eav\Model\Entity\Type', [], [], '', false);
        $formElement = $this->getMock('Magento\Framework\Data\Form\Element\Text', ['setDisabled'], [], '', false);
        $directoryReadInterface = $this->getMock('\Magento\Framework\Filesystem\Directory\ReadInterface');

        $this->registry->expects($this->any())->method('registry')->with('entity_attribute')
            ->willReturn($attributeModel);
        $this->formFactory->expects($this->any())->method('create')->willReturn($form);
        $form->expects($this->any())->method('addFieldset')->willReturn($fieldSet);
        $form->expects($this->any())->method('getElement')->willReturn($formElement);
        $fieldSet->expects($this->any())->method('addField')->willReturnSelf();
        $attributeModel->expects($this->any())->method('getDefaultValue')->willReturn('default_value');
        $attributeModel->expects($this->any())->method('setDisabled')->willReturnSelf();
        $attributeModel->expects($this->any())->method('getId')->willReturn(1);
        $attributeModel->expects($this->any())->method('getEntityType')->willReturn($entityType);
        $attributeModel->expects($this->any())->method('getIsUserDefined')->willReturn(false);
        $attributeModel->expects($this->any())->method('getAttributeCode')->willReturn('attribute_code');
        $this->localeDate->expects($this->any())->method('getDateFormat')->willReturn('mm/dd/yy');
        $entityType->expects($this->any())->method('getEntityTypeCode')->willReturn('entity_type_code');
        $this->eavData->expects($this->any())->method('getFrontendClasses')->willReturn([]);
        $formElement->expects($this->exactly(2))->method('setDisabled')->willReturnSelf();
        $this->yesNo->expects($this->any())->method('toOptionArray')->willReturn(['yes', 'no']);
        $this->filesystem->expects($this->any())->method('getDirectoryRead')->willReturn($directoryReadInterface);
        $directoryReadInterface->expects($this->any())->method('getRelativePath')->willReturn('relative_path');
        $this->propertyLocker->expects($this->once())->method('lock')->with($form);

        $this->block->setData(['action' => 'save']);
        $this->block->toHtml();
    }
}
