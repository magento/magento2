<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdvancedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Block\Adminhtml\Product\Attribute\Grid
     */
    protected $block;

    /**
     * @var \Magento\Framework\Data\FormFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $formFactory;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $localeDate;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $yesNo;

    /**
     * @var \Magento\Eav\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eavData;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $filesystem;

    /**
     * @var PropertyLocker|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $propertyLocker;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->registry = $this->createMock(\Magento\Framework\Registry::class);
        $this->formFactory = $this->createMock(\Magento\Framework\Data\FormFactory::class);
        $this->yesNo = $this->createMock(\Magento\Config\Model\Config\Source\Yesno::class);
        $this->localeDate = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $this->eavData = $this->createMock(\Magento\Eav\Helper\Data::class);
        $this->filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->propertyLocker = $this->createMock(PropertyLocker::class);

        $this->block = $objectManager->getObject(
            \Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Advanced::class,
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
        $fieldSet = $this->createMock(\Magento\Framework\Data\Form\Element\Fieldset::class);
        $form = $this->createMock(\Magento\Framework\Data\Form::class);
        $attributeModel = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            ['getDefaultValue', 'setDisabled', 'getId', 'getEntityType', 'getIsUserDefined', 'getAttributeCode']
        );
        $entityType = $this->createMock(\Magento\Eav\Model\Entity\Type::class);
        $formElement = $this->createPartialMock(\Magento\Framework\Data\Form\Element\Text::class, ['setDisabled']);
        $directoryReadInterface = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class);

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
