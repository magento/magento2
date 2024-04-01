<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Advanced;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker;
use Magento\Eav\Helper\Data as EavHelper;
use Magento\Eav\Model\Entity\Type as EntityType;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\Form\Element\Text;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test product attribute add/edit advanced form tab
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdvancedTest extends TestCase
{
    /**
     * @var Advanced
     */
    protected $block;

    /**
     * @var FormFactory|MockObject
     */
    protected $formFactory;

    /**
     * @var Registry|MockObject
     */
    protected $registry;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $localeDate;

    /**
     * @var Yesno|MockObject
     */
    protected $yesNo;

    /**
     * @var EavHelper|MockObject
     */
    protected $eavData;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystem;

    /**
     * @var PropertyLocker|MockObject
     */
    protected $propertyLocker;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $objects = [
            [
                JsonHelper::class,
                $this->createMock(JsonHelper::class)
            ],
            [
                DirectoryHelper::class,
                $this->createMock(DirectoryHelper::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);

        $this->registry = $this->createMock(Registry::class);
        $this->formFactory = $this->createMock(FormFactory::class);
        $this->yesNo = $this->createMock(Yesno::class);
        $this->localeDate = $this->getMockForAbstractClass(TimezoneInterface::class);
        $this->eavData = $this->createMock(EavHelper::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->propertyLocker = $this->createMock(PropertyLocker::class);

        $this->block = $objectManager->getObject(
            Advanced::class,
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

    /**
     * Test the block's html output
     */
    public function testToHtml()
    {
        $defaultValue = 'default_value';
        $localizedDefaultValue = 'localized_default_value';
        $frontendInput = 'datetime';
        $dateFormat = 'mm/dd/yy';
        $timeFormat = 'H:i:s:';
        $timeZone = 'America/Chicago';

        $fieldSet = $this->createMock(Fieldset::class);
        $form = $this->createMock(Form::class);
        $attributeModel = $this->getMockBuilder(Attribute::class)
            ->addMethods(['setDisabled'])
            ->onlyMethods(
                [
                    'getDefaultValue',
                    'getId',
                    'getEntityType',
                    'getIsUserDefined',
                    'getAttributeCode',
                    'getFrontendInput'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $entityType = $this->createMock(EntityType::class);
        $formElement = $this->getMockBuilder(Text::class)
            ->addMethods(['setDisabled'])
            ->disableOriginalConstructor()
            ->getMock();
        $directoryReadInterface = $this->getMockForAbstractClass(ReadInterface::class);

        $this->registry->expects($this->any())->method('registry')->with('entity_attribute')
            ->willReturn($attributeModel);
        $this->formFactory->expects($this->any())->method('create')->willReturn($form);
        $form->expects($this->any())->method('addFieldset')->willReturn($fieldSet);
        $form->expects($this->any())->method('getElement')->willReturn($formElement);
        $fieldSet->expects($this->any())->method('addField')->willReturnSelf();
        $attributeModel->expects($this->any())->method('getDefaultValue')->willReturn($defaultValue);
        $attributeModel->expects($this->any())->method('setDisabled')->willReturnSelf();
        $attributeModel->expects($this->any())->method('getId')->willReturn(1);
        $attributeModel->expects($this->any())->method('getEntityType')->willReturn($entityType);
        $attributeModel->expects($this->any())->method('getIsUserDefined')->willReturn(false);
        $attributeModel->expects($this->any())->method('getAttributeCode')->willReturn('attribute_code');
        $attributeModel->expects($this->any())->method('getFrontendInput')->willReturn($frontendInput);

        $dateTimeMock = $this->createMock(\DateTime::class);
        $dateTimeMock->expects($this->once())->method('setTimezone')->with(new \DateTimeZone($timeZone));
        $dateTimeMock->expects($this->once())
            ->method('format')
            ->with(DateTime::DATETIME_PHP_FORMAT)
            ->willReturn($localizedDefaultValue);
        $this->localeDate->expects($this->any())->method('getDateFormat')->willReturn($dateFormat);
        $this->localeDate->expects($this->any())->method('getTimeFormat')->willReturn($timeFormat);
        $this->localeDate->expects($this->once())->method('getConfigTimezone')->willReturn($timeZone);
        $this->localeDate->expects($this->once())
            ->method('date')
            ->with($defaultValue, null, false)
            ->willReturn($dateTimeMock);

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
