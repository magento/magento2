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
use Magento\Framework\Json\Helper\Data as JsonHelper;
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
     * @inheritDoc
     */
    protected function setUp(): void
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
            ->willReturnMap([
                [ElementCreator::class, $objectManager->getObject(ElementCreator::class)],
                [JsonHelper::class, $objectManager->getObject(JsonHelper::class)]
            ]);
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

        $fieldsetMock = $this->createMock(Fieldset::class);
        $formMock = $this->createMock(Form::class);
        $attributeModelMock = $this->getMockBuilder(Attribute::class)
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
        $entityTypeMock = $this->createMock(EntityType::class);
        $formElementMock = $this->getMockBuilder(Text::class)
            ->addMethods(['setDisabled'])
            ->disableOriginalConstructor()
            ->getMock();
        $directoryReadInterfaceMock = $this->getMockForAbstractClass(ReadInterface::class);

        $this->registryMock->method('registry')->with('entity_attribute')
            ->willReturn($attributeModelMock);
        $this->formFactoryMock->method('create')->willReturn($formMock);
        $formMock->method('addFieldset')->willReturn($fieldsetMock);
        $formMock->method('getElement')->willReturn($formElementMock);
        $fieldsetMock->method('addField')->willReturnSelf();
        $attributeModelMock->method('getDefaultValue')->willReturn($defaultValue);
        $attributeModelMock->method('setDisabled')->willReturnSelf();
        $attributeModelMock->method('getId')->willReturn(1);
        $attributeModelMock->method('getEntityType')->willReturn($entityTypeMock);
        $attributeModelMock->method('getIsUserDefined')->willReturn(false);
        $attributeModelMock->method('getAttributeCode')->willReturn('attribute_code');
        $attributeModelMock->method('getFrontendInput')->willReturn($frontendInput);

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

        $entityTypeMock->method('getEntityTypeCode')->willReturn('entity_type_code');
        $this->eavDataMock->method('getFrontendClasses')->willReturn([]);
        $formElementMock->expects($this->exactly(3))->method('setDisabled')->willReturnSelf();
        $this->yesNoMock->method('toOptionArray')->willReturn(['yes', 'no']);
        $this->filesystemMock->method('getDirectoryRead')->willReturn($directoryReadInterfaceMock);
        $directoryReadInterfaceMock->method('getRelativePath')->willReturn('relative_path');
        $this->propertyLockerMock->expects($this->once())->method('lock')->with($formMock);

        $this->block->setData(['action' => 'save']);
        $this->block->toHtml();
    }
}
