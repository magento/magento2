<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Test\Unit\Block\System\Config;

use Magento\Config\Model\Config\Reader\Source\Deployed\SettingChecker;
use Magento\Config\Model\Config\Structure\ElementVisibilityInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Test System config form block
 *
 * @package Magento\Config\Test\Unit\Block\System\Config
 */

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject_MockBuilder
     */
    protected $_objectBuilder;

    /**
     * @var \Magento\Config\Block\System\Config\Form
     */
    protected $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_systemConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_formMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_fieldFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_urlModelMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_formFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_backendConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_coreConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_fieldsetFactoryMock;

    /**
     * @var StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerMock;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->_systemConfigMock = $this->createMock(\Magento\Config\Model\Config\Structure::class);

        $requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $requestParams = [
            ['website', '', 'website_code'],
            ['section', '', 'section_code'],
            ['store', '', 'store_code'],
        ];
        $requestMock->expects($this->any())->method('getParam')->willReturnMap($requestParams);

        $layoutMock = $this->createMock(\Magento\Framework\View\Layout::class);

        $this->_urlModelMock = $this->createMock(\Magento\Backend\Model\Url::class);
        $configFactoryMock = $this->createMock(\Magento\Config\Model\Config\Factory::class);
        $this->_formFactoryMock = $this->createPartialMock(\Magento\Framework\Data\FormFactory::class, ['create']);
        $this->_fieldsetFactoryMock = $this->createMock(
            \Magento\Config\Block\System\Config\Form\Fieldset\Factory::class
        );
        $this->_fieldFactoryMock = $this->createMock(\Magento\Config\Block\System\Config\Form\Field\Factory::class);
        $settingCheckerMock = $this->getMockBuilder(SettingChecker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_coreConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->_backendConfigMock = $this->createMock(\Magento\Config\Model\Config::class);

        $configFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            ['data' => ['section' => 'section_code', 'website' => 'website_code', 'store' => 'store_code']]
        )->willReturn(
            $this->_backendConfigMock
        );

        $this->_backendConfigMock->expects(
            $this->once()
        )->method(
            'load'
        )->willReturn(
            ['section1/group1/field1' => 'some_value']
        );

        $this->_formMock = $this->createPartialMock(
            \Magento\Framework\Data\Form::class,
            ['setParent', 'setBaseUrl', 'addFieldset']
        );

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $context = $helper->getObject(
            \Magento\Backend\Block\Template\Context::class,
            [
                'scopeConfig' => $this->_coreConfigMock,
                'request' => $requestMock,
                'urlBuilder' => $this->_urlModelMock,
                'storeManager' => $this->storeManagerMock
            ]
        );

        $data = [
            'request' => $requestMock,
            'layout' => $layoutMock,
            'configStructure' => $this->_systemConfigMock,
            'configFactory' => $configFactoryMock,
            'formFactory' => $this->_formFactoryMock,
            'fieldsetFactory' => $this->_fieldsetFactoryMock,
            'fieldFactory' => $this->_fieldFactoryMock,
            'context' => $context,
            'settingChecker' => $settingCheckerMock,
        ];

        $objectArguments = $helper->getConstructArguments(\Magento\Config\Block\System\Config\Form::class, $data);
        $this->_objectBuilder = $this->getMockBuilder(\Magento\Config\Block\System\Config\Form::class)
            ->setConstructorArgs($objectArguments)
            ->setMethods(['something']);
        $deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $deploymentConfigMock->expects($this->any())
            ->method('get')
            ->willReturn([]);

        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [DeploymentConfig::class, $deploymentConfigMock]
            ]);
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);
        $this->object = $helper->getObject(\Magento\Config\Block\System\Config\Form::class, $data);
        $this->object->setData('scope_id', 1);
    }

    /**
     * @param bool $sectionIsVisible
     * @dataProvider initFormDataProvider
     */
    public function testInitForm($sectionIsVisible)
    {
        /** @var \Magento\Config\Block\System\Config\Form | \PHPUnit\Framework\MockObject\MockObject $object */
        $object = $this->_objectBuilder->setMethods(['_initGroup'])->getMock();
        $object->setData('scope_id', 1);
        $this->_formFactoryMock->expects($this->any())->method('create')->willReturn($this->_formMock);
        $this->_formMock->expects($this->once())->method('setParent')->with($object);
        $this->_formMock->expects($this->once())->method('setBaseUrl')->with('base_url');
        $this->_urlModelMock->expects($this->any())->method('getBaseUrl')->willReturn('base_url');

        $sectionMock = $this->getMockBuilder(\Magento\Config\Model\Config\Structure\Element\Section::class)
            ->disableOriginalConstructor()
            ->getMock();
        if ($sectionIsVisible) {
            $sectionMock->expects($this->once())
                ->method('isVisible')
                ->willReturn(true);
            $sectionMock->expects($this->once())
                ->method('getChildren')
                ->willReturn([
                    $this->createMock(\Magento\Config\Model\Config\Structure\Element\Group::class)
                ]);
        }

        $this->_systemConfigMock->expects(
            $this->once()
        )->method(
            'getElement'
        )->with(
            'section_code'
        )->willReturn(
            $sectionIsVisible ? $sectionMock : null
        );

        if ($sectionIsVisible) {
            $object->expects($this->once())
                ->method('_initGroup');
        } else {
            $object->expects($this->never())
                ->method('_initGroup');
        }

        $object->initForm();
        $this->assertEquals($this->_formMock, $object->getForm());
    }

    /**
     * @return array
     */
    public function initFormDataProvider()
    {
        return [
            [false],
            [true]
        ];
    }

    /**
     * @param bool $shouldCloneFields
     * @param array $prefixes
     * @param int $callNum
     * @dataProvider initGroupDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testInitGroup($shouldCloneFields, $prefixes, $callNum)
    {
        /** @var \Magento\Config\Block\System\Config\Form | \PHPUnit\Framework\MockObject\MockObject $object */
        $object = $this->_objectBuilder->setMethods(['initFields'])->getMock();
        $this->_formFactoryMock->expects($this->any())->method('create')->willReturn($this->_formMock);
        $this->_formMock->expects($this->once())->method('setParent')->with($object);
        $this->_formMock->expects($this->once())->method('setBaseUrl')->with('base_url');
        $this->_urlModelMock->expects($this->any())->method('getBaseUrl')->willReturn('base_url');

        $fieldsetRendererMock = $this->createMock(\Magento\Config\Block\System\Config\Form\Fieldset::class);
        $this->_fieldsetFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->willReturn(
            $fieldsetRendererMock
        );

        $cloneModelMock = $this->createPartialMock(\Magento\Config\Model\Config::class, ['getPrefixes']);

        $groupMock = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Group::class);
        $groupMock->expects($this->once())->method('getFrontendModel')->willReturn(false);
        $groupMock->expects($this->any())->method('getPath')->willReturn('section_id_group_id');
        $groupMock->expects($this->once())->method('getLabel')->willReturn('label');
        $groupMock->expects($this->once())->method('getComment')->willReturn('comment');
        $groupMock->expects($this->once())->method('isExpanded')->willReturn(false);
        $groupMock->expects($this->once())->method('populateFieldset');
        $groupMock->expects($this->once())->method('shouldCloneFields')->willReturn($shouldCloneFields);
        $groupMock->expects($this->once())->method('getData')->willReturn('some group data');
        $groupMock->expects(
            $this->once()
        )->method(
            'getDependencies'
        )->with(
            'store_code'
        )->willReturn(
            []
        );

        $sectionMock = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Section::class);

        $sectionMock->expects($this->once())->method('isVisible')->willReturn(true);
        $sectionMock->expects($this->once())->method('getChildren')->willReturn([$groupMock]);

        $this->_systemConfigMock->expects(
            $this->once()
        )->method(
            'getElement'
        )->with(
            'section_code'
        )->willReturn(
            $sectionMock
        );

        $formFieldsetMock = $this->createMock(\Magento\Framework\Data\Form\Element\Fieldset::class);

        $params = [
            'legend' => 'label',
            'comment' => 'comment',
            'expanded' => false,
            'group' => 'some group data',
        ];
        $this->_formMock->expects(
            $this->once()
        )->method(
            'addFieldset'
        )->with(
            'section_id_group_id',
            $params
        )->willReturn(
            $formFieldsetMock
        );

        if ($shouldCloneFields) {
            $cloneModelMock->expects($this->once())->method('getPrefixes')->willReturn($prefixes);

            $groupMock->expects($this->once())->method('getCloneModel')->willReturn($cloneModelMock);
        }

        if ($shouldCloneFields && $prefixes) {
            $object->expects($this->exactly($callNum))
                ->method('initFields')
                ->with(
                    $formFieldsetMock,
                    $groupMock,
                    $sectionMock,
                    $prefixes[0]['field'],
                    $prefixes[0]['label']
                );
        } else {
            $object->expects($this->exactly($callNum))
                ->method('initFields')
                ->with($formFieldsetMock, $groupMock, $sectionMock);
        }

        $object->initForm();
    }

    /**
     * @return array
     */
    public function initGroupDataProvider()
    {
        return [
            [true, [['field' => 'field', 'label' => 'label']], 1],
            [true, [], 0],
            [false, [['field' => 'field', 'label' => 'label']], 1],
        ];
    }

    /**
     * @param array $backendConfigValue
     * @param string|bool $configValue
     * @param string|null $configPath
     * @param bool $inherit
     * @param string $expectedValue
     * @param string|null $placeholderValue
     * @param int $hasBackendModel
     * @param bool $isDisabled
     * @param bool $isReadOnly
     * @param bool $expectedDisable
     *
     * @dataProvider initFieldsDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function testInitFields(
        $backendConfigValue,
        $configValue,
        $configPath,
        $inherit,
        $expectedValue,
        $placeholderValue,
        $hasBackendModel,
        $isDisabled,
        $isReadOnly,
        $expectedDisable
    ) {
        // Parameters initialization
        $fieldsetMock = $this->createMock(\Magento\Framework\Data\Form\Element\Fieldset::class);
        $groupMock = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Group::class);
        $sectionMock = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Section::class);
        $fieldPrefix = 'fieldPrefix';
        $labelPrefix = 'labelPrefix';

        // Field Renderer Mock configuration
        $fieldRendererMock = $this->createMock(\Magento\Config\Block\System\Config\Form\Field::class);
        $this->_fieldFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->willReturn(
            $fieldRendererMock
        );

        $this->_backendConfigMock->expects(
            $this->once()
        )->method(
            'extendConfig'
        )->with(
            'some/config/path',
            false,
            ['section1/group1/field1' => 'some_value']
        )->willReturn(
            $backendConfigValue
        );

        $this->_coreConfigMock->expects(
            $this->any()
        )->method(
            'getValue'
        )->with(
            $configPath
        )->willReturn(
            $configValue
        );

        /** @var \Magento\Store\Api\Data\StoreInterface|\PHPUnit\Framework\MockObject\MockObject $storeMock */
        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->getMockForAbstractClass();
        $storeMock->expects($this->once())
            ->method('getCode')
            ->willReturn('store_code');

        $this->storeManagerMock->expects($this->atLeastOnce())
            ->method('getStore')
            ->with('store_code')
            ->willReturn($storeMock);

        // Field mock configuration
        $fieldMock = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Field::class);
        $fieldMock->expects($this->any())->method('getPath')->willReturn('section1/group1/field1');
        $fieldMock->expects($this->any())->method('getConfigPath')->willReturn($configPath);
        $fieldMock->expects($this->any())->method('getGroupPath')->willReturn('some/config/path');
        $fieldMock->expects($this->once())->method('getSectionId')->willReturn('some_section');

        $fieldMock->expects(
            $this->exactly($hasBackendModel)
        )->method(
            'hasBackendModel'
        )->willReturn(
            false
        );
        $fieldMock->expects(
            $this->once()
        )->method(
            'getDependencies'
        )->with(
            $fieldPrefix
        )->willReturn(
            []
        );
        $fieldMock->expects($this->any())->method('getType')->willReturn('field');
        $fieldMock->expects($this->once())->method('getLabel')->willReturn('label');
        $fieldMock->expects($this->once())->method('getComment')->willReturn('comment');
        $fieldMock->expects($this->once())->method('getTooltip')->willReturn('tooltip');
        $fieldMock->expects($this->once())->method('getHint')->willReturn('hint');
        $fieldMock->expects($this->once())->method('getFrontendClass')->willReturn('frontClass');
        $fieldMock->expects($this->once())->method('showInDefault')->willReturn(false);
        $fieldMock->expects($this->any())->method('showInWebsite')->willReturn(false);
        $fieldMock->expects($this->once())->method('getData')->willReturn('fieldData');
        $fieldMock->expects($this->any())->method('getRequiredFields')->willReturn([]);
        $fieldMock->expects($this->any())->method('getRequiredGroups')->willReturn([]);

        $fields = [$fieldMock];
        $groupMock->expects($this->once())->method('getChildren')->willReturn($fields);

        $sectionMock->expects($this->once())->method('getId')->willReturn('section1');

        $formFieldMock = $this->getMockForAbstractClass(
            \Magento\Framework\Data\Form\Element\AbstractElement::class,
            [],
            '',
            false,
            false,
            true,
            ['setRenderer']
        );

        $params = [
            'name' => 'groups[group1][fields][fieldPrefixfield1][value]',
            'label' => 'label',
            'comment' => 'comment',
            'tooltip' => 'tooltip',
            'hint' => 'hint',
            'value' => $expectedValue,
            'inherit' => $inherit,
            'class' => 'frontClass',
            'field_config' => 'fieldData',
            'scope' => 'stores',
            'scope_id' => 1,
            'scope_label' => __('[GLOBAL]'),
            'can_use_default_value' => false,
            'can_use_website_value' => false,
            'can_restore_to_default' => false,
            'disabled' => $expectedDisable,
            'is_disable_inheritance' => $expectedDisable
        ];

        $formFieldMock->expects($this->once())->method('setRenderer')->with($fieldRendererMock);

        $fieldsetMock->expects(
            $this->once()
        )->method(
            'addField'
        )->with(
            'section1_group1_field1',
            'field',
            $params
        )->willReturn(
            $formFieldMock
        );

        $fieldMock->expects($this->once())->method('populateInput');

        $settingCheckerMock = $this->getMockBuilder(SettingChecker::class)
            ->disableOriginalConstructor()
            ->getMock();
        $settingCheckerMock->expects($this->any())
            ->method('isReadOnly')
            ->willReturn($isReadOnly);
        $settingCheckerMock->expects($this->once())
            ->method('getPlaceholderValue')
            ->willReturn($placeholderValue);

        $elementVisibilityMock = $this->getMockBuilder(ElementVisibilityInterface::class)
            ->getMockForAbstractClass();
        $elementVisibilityMock->expects($this->any())
            ->method('isDisabled')
            ->willReturn($isDisabled);

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $helper->setBackwardCompatibleProperty($this->object, 'settingChecker', $settingCheckerMock);
        $helper->setBackwardCompatibleProperty($this->object, 'elementVisibility', $elementVisibilityMock);

        $this->object->initFields($fieldsetMock, $groupMock, $sectionMock, $fieldPrefix, $labelPrefix);
    }

    /**
     * @return array
     */
    public function initFieldsDataProvider()
    {
        return [
            [
                ['section1/group1/field1' => 'some_value'],
                'some_value',
                'section1/group1/field1',
                false,
                'some_value',
                null,
                1,
                false,
                false,
                false
            ],
            [
                [],
                'Config Value',
                'some/config/path',
                true,
                'Config Value',
                null,
                1,
                true,
                false,
                true
            ],
            [
                [],
                'Config Value',
                'some/config/path',
                true,
                'Placeholder Value',
                'Placeholder Value',
                0,
                false,
                true,
                true
            ],
            [
                [],
                'Config Value',
                'some/config/path',
                true,
                'Placeholder Value',
                'Placeholder Value',
                0,
                true,
                true,
                true
            ]
        ];
    }
}
