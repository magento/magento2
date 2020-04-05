<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Attribute\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Element\ElementCreator;
use Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Advanced;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker;
use Magento\Eav\Helper\Data as EavHelper;
use Magento\Eav\Model\Entity\Type as EntityType;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\Form\Element\Text;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\ObjectManagerInterface;
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
    private $block;

    /**
     * @var FormFactory|MockObject
     */
    private $formFactoryMock;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $localeDateMock;

    /**
     * @var Yesno|MockObject
     */
    private $yesNoMock;

    /**
     * @var EavHelper|MockObject
     */
    private $eavDataMock;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var PropertyLocker|MockObject
     */
    private $propertyLockerMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->registryMock = $this->createMock(Registry::class);
        $this->formFactoryMock = $this->createMock(FormFactory::class);
        $this->yesNoMock = $this->createMock(Yesno::class);
        $this->localeDateMock = $this->createMock(TimezoneInterface::class);
        $this->eavDataMock = $this->createMock(EavHelper::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->propertyLockerMock = $this->createMock(PropertyLocker::class);

        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $objectManagerMock->method('get')
            ->with(ElementCreator::class)
            ->willReturn(
                $objectManager->getObject(ElementCreator::class)
            );
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);

        $this->block = $objectManager->getObject(
            Advanced::class,
            [
                'registry' => $this->registryMock,
                'formFactory' => $this->formFactoryMock,
                'localeDate' => $this->localeDateMock,
                'yesNo' => $this->yesNoMock,
                'eavData' => $this->eavDataMock,
                'filesystem' => $this->filesystemMock,
                'propertyLocker' => $this->propertyLockerMock
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
        $attributeModel = $this->createPartialMock(
            Attribute::class,
            [
                'getDefaultValue',
                'setDisabled',
                'getId',
                'getEntityType',
                'getIsUserDefined',
                'getAttributeCode',
                'getFrontendInput'
            ]
        );
        $entityType = $this->createMock(EntityType::class);
        $formElement = $this->createPartialMock(Text::class, ['setDisabled']);
        $directoryReadInterface = $this->createMock(ReadInterface::class);

        $this->registryMock->method('registry')->with('entity_attribute')
            ->willReturn($attributeModel);
        $this->formFactoryMock->method('create')->willReturn($form);
        $form->method('addFieldset')->willReturn($fieldSet);
        $form->method('getElement')->willReturn($formElement);
        $fieldSet->method('addField')->willReturnSelf();
        $attributeModel->method('getDefaultValue')->willReturn($defaultValue);
        $attributeModel->method('setDisabled')->willReturnSelf();
        $attributeModel->method('getId')->willReturn(1);
        $attributeModel->method('getEntityType')->willReturn($entityType);
        $attributeModel->method('getIsUserDefined')->willReturn(false);
        $attributeModel->method('getAttributeCode')->willReturn('attribute_code');
        $attributeModel->method('getFrontendInput')->willReturn($frontendInput);

        $dateTimeMock = $this->createMock(\DateTime::class);
        $dateTimeMock->expects($this->once())->method('setTimezone')->with(new \DateTimeZone($timeZone));
        $dateTimeMock->expects($this->once())
            ->method('format')
            ->with(DateTime::DATETIME_PHP_FORMAT)
            ->willReturn($localizedDefaultValue);
        $this->localeDateMock->method('getDateFormat')->willReturn($dateFormat);
        $this->localeDateMock->method('getTimeFormat')->willReturn($timeFormat);
        $this->localeDateMock->expects($this->once())->method('getConfigTimezone')->willReturn($timeZone);
        $this->localeDateMock->expects($this->once())
            ->method('date')
            ->with($defaultValue, null, false)
            ->willReturn($dateTimeMock);

        $entityType->method('getEntityTypeCode')->willReturn('entity_type_code');
        $this->eavDataMock->method('getFrontendClasses')->willReturn([]);
        $formElement->expects($this->exactly(3))->method('setDisabled')->willReturnSelf();
        $this->yesNoMock->method('toOptionArray')->willReturn(['yes', 'no']);
        $this->filesystemMock->method('getDirectoryRead')->willReturn($directoryReadInterface);
        $directoryReadInterface->method('getRelativePath')->willReturn('relative_path');
        $this->propertyLockerMock->expects($this->once())->method('lock')->with($form);

        $this->block->setData(['action' => 'save']);
        $this->block->toHtml();
    }
}
