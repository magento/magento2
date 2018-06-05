<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Block\System\Config;

use Magento\Framework\App\Cache\State;

/**
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $_formFactory;

    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Section
     */
    protected $_section;

    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Group
     */
    protected $_group;

    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Field
     */
    protected $_field;

    /**
     * @var array
     */
    protected $_configData;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_formFactory = $this->_objectManager->create('Magento\Framework\Data\FormFactory');
    }

    public function testDependenceHtml()
    {
        /** @var $layout \Magento\Framework\View\LayoutInterface */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Layout',
            ['area' => 'adminhtml']
        );
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Config\ScopeInterface'
        )->setCurrentScope(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
        );
        /** @var $block \Magento\Config\Block\System\Config\Form */
        $block = $layout->createBlock('Magento\Config\Block\System\Config\Form', 'block');

        /** @var $childBlock \Magento\Framework\View\Element\Text */
        $childBlock = $layout->addBlock('Magento\Framework\View\Element\Text', 'element_dependence', 'block');

        $expectedValue = 'dependence_html_relations';
        $this->assertNotContains($expectedValue, $block->toHtml());

        $childBlock->setText($expectedValue);
        $this->assertContains($expectedValue, $block->toHtml());
    }

    /**
     * @covers \Magento\Config\Block\System\Config\Form::initFields
     * @param string $fieldId uses the test_field_use_config field if true
     * @param bool $isConfigDataEmpty if the config data array should be empty or not
     * @param string $configDataValue The value that the field path should be set to in the config data
     * @param int $valueSelCtr Number of time that value is selected
     * @param bool $expectedUseDefault
     * @dataProvider initFieldsUseDefaultCheckboxDataProvider
     */
    public function testInitFieldsUseDefaultCheckbox(
        $fieldId,
        $isConfigDataEmpty,
        $configDataValue,
        $expectedUseDefault,
        $valueSelCtr = 1
    ) {
        $this->_setupFieldsInheritCheckbox($fieldId, $isConfigDataEmpty, $configDataValue);

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Config\ScopeInterface'
        )->setCurrentScope(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
        );
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset($this->_section->getId() . '_' . $this->_group->getId(), []);

        /* @TODO Eliminate stub by proper mock / config fixture usage */
        /** @var $block \Magento\Config\Block\System\Config\FormStub */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Config\Block\System\Config\FormStub'
        );
        $block->setScope(\Magento\Config\Block\System\Config\Form::SCOPE_WEBSITES);
        $block->setStubConfigData($this->_configData);
        $block->initFields($fieldset, $this->_group, $this->_section);

        $fieldsetSel = 'fieldset';
        $valueSel = sprintf(
            'input#%s_%s_%s',
            $this->_section->getId(),
            $this->_group->getId(),
            $this->_field->getId()
        );
        $valueDisabledSel = sprintf('%s[disabled="disabled"]', $valueSel);
        $useDefaultSel = sprintf(
            'input#%s_%s_%s_inherit.checkbox',
            $this->_section->getId(),
            $this->_group->getId(),
            $this->_field->getId()
        );
        $useDefaultCheckedSel = sprintf('%s[checked="checked"]', $useDefaultSel);
        $fieldsetHtml = $fieldset->getElementHtml();

        $this->assertSelectCount($fieldsetSel, true, $fieldsetHtml, 'Fieldset HTML is invalid');
        $this->assertSelectCount(
            $valueSel,
            $valueSelCtr,
            $fieldsetHtml,
            'Field input should appear ' . $valueSelCtr . ' times in fieldset HTML'
        );
        $this->assertSelectCount(
            $useDefaultSel,
            $valueSelCtr,
            $fieldsetHtml,
            '"Use Default" checkbox should appear' . $valueSelCtr . ' times  in fieldset HTML.'
        );

        if ($expectedUseDefault) {
            $this->assertSelectCount(
                $useDefaultCheckedSel,
                true,
                $fieldsetHtml,
                '"Use Default" checkbox should be checked'
            );
            $this->assertSelectCount($valueDisabledSel, true, $fieldsetHtml, 'Field input should be disabled');
        } else {
            $this->assertSelectCount(
                $useDefaultCheckedSel,
                false,
                $fieldsetHtml,
                '"Use Default" checkbox should not be checked'
            );
            $this->assertSelectCount($valueDisabledSel, false, $fieldsetHtml, 'Field input should not be disabled');
        }
    }

    /**
     * @return array
     */
    public static function initFieldsUseDefaultCheckboxDataProvider()
    {
        return [
            ['test_field', true, null, true],
            ['test_field', false, null, false],
            ['test_field', false, '', false],
            ['test_field', false, 'value', false],
            ['test_field_use_config_module_1', false, 'config value', false],
            ['test_field_use_config_module_0', false, 'config value', false, 0],
        ];
    }

    /**
     * @covers \Magento\Config\Block\System\Config\Form::initFields
     * @param string $fieldId uses the test_field_use_config field if true
     * @param bool $isConfigDataEmpty if the config data array should be empty or not
     * @param string $configDataValue Value that the field path should be set to in the config data
     * @param int $valueSelCtr Number of time that value is selected
     * @dataProvider initFieldsUseConfigPathDataProvider
     * @magentoConfigFixture default/test_config_section/test_group_config_node/test_field_value config value
     */
    public function testInitFieldsUseConfigPath($fieldId, $isConfigDataEmpty, $configDataValue, $valueSelCtr = 1)
    {
        $this->_setupFieldsInheritCheckbox($fieldId, $isConfigDataEmpty, $configDataValue);

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Config\ScopeInterface'
        )->setCurrentScope(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
        );
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset($this->_section->getId() . '_' . $this->_group->getId(), []);

        /* @TODO Eliminate stub by proper mock / config fixture usage */
        /** @var $block \Magento\Config\Block\System\Config\FormStub */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Config\Block\System\Config\FormStub'
        );
        $block->setScope(\Magento\Config\Block\System\Config\Form::SCOPE_DEFAULT);
        $block->setStubConfigData($this->_configData);
        $block->initFields($fieldset, $this->_group, $this->_section);

        $fieldsetSel = 'fieldset';
        $valueSel = sprintf(
            'input#%s_%s_%s',
            $this->_section->getId(),
            $this->_group->getId(),
            $this->_field->getId()
        );
        $fieldsetHtml = $fieldset->getElementHtml();

        $this->assertSelectCount($fieldsetSel, true, $fieldsetHtml, 'Fieldset HTML is invalid');
        $this->assertSelectCount(
            $valueSel,
            $valueSelCtr,
            $fieldsetHtml,
            'Field input should appear ' . $valueSelCtr . ' times in fieldset HTML'
        );
    }

    /**
     * @return array
     */
    public static function initFieldsUseConfigPathDataProvider()
    {
        return [
            ['test_field', true, null],
            ['test_field', false, null],
            ['test_field', false, ''],
            ['test_field', false, 'value'],
            ['test_field_use_config_module_1', false, 'config value'],
            ['test_field_use_config_module_0', false, 'config value', 0]
        ];
    }

    /**
     * @param string $fieldId uses the test_field_use_config field if true
     * @param bool $isConfigDataEmpty if the config data array should be empty or not
     * @param string $configDataValue the value that the field path should be set to in the config data
     */
    protected function _setupFieldsInheritCheckbox($fieldId, $isConfigDataEmpty, $configDataValue)
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize([
            State::PARAM_BAN_CACHE => true,
        ]);
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\Config\ScopeInterface')
            ->setCurrentScope(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\AreaList')
            ->getArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE)
            ->load(\Magento\Framework\App\Area::PART_CONFIG);

        $fileResolverMock = $this->getMockBuilder(
            'Magento\Framework\App\Config\FileResolver'
        )->disableOriginalConstructor()->getMock();
        $fileIteratorFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Config\FileIteratorFactory'
        );
        $fileIterator = $fileIteratorFactory->create(
            [__DIR__ . '/_files/test_section_config.xml']
        );
        $fileResolverMock->expects($this->any())->method('get')->will($this->returnValue($fileIterator));

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $structureReader = $objectManager->create(
            'Magento\Config\Model\Config\Structure\Reader',
            ['fileResolver' => $fileResolverMock]
        );
        $structureData = $objectManager->create(
            'Magento\Config\Model\Config\Structure\Data',
            ['reader' => $structureReader]
        );
        /** @var \Magento\Config\Model\Config\Structure $structure  */
        $structure = $objectManager->create(
            'Magento\Config\Model\Config\Structure',
            ['structureData' => $structureData]
        );

        $this->_section = $structure->getElement('test_section');

        $this->_group = $structure->getElement('test_section/test_group');

        $this->_field = $structure->getElement('test_section/test_group/' . $fieldId);

        $fieldPath = $this->_field->getConfigPath();

        if ($isConfigDataEmpty) {
            $this->_configData = [];
        } else {
            $this->_configData = [$fieldPath => $configDataValue];
        }
    }

    public function testInitFormAddsFieldsets()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\ResponseInterface'
        )->headersSentThrowsException = false;
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\RequestInterface'
        )->setParam(
            'section',
            'general'
        );
        /** @var $block \Magento\Config\Block\System\Config\Form */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Config\Block\System\Config\Form'
        );
        $block->initForm();
        $expectedIds = [
            'general_country' => [
                'general_country_default' => 'select',
                'general_country_allow' => 'select',
                'general_country_optional_zip_countries' => 'select',
                'general_country_eu_countries' => 'select',
            ],
            'general_region' => [
                'general_region_state_required' => 'select',
                'general_region_display_all' => 'select',
            ],
            'general_locale' => [
                'general_locale_timezone' => 'select',
                'general_locale_code' => 'select',
                'general_locale_firstday' => 'select',
                'general_locale_weekend' => 'select',
            ],
            'general_restriction' => [
                'general_restriction_is_active' => 'select',
                'general_restriction_mode' => 'select',
                'general_restriction_http_redirect' => 'select',
                'general_restriction_cms_page' => 'select',
                'general_restriction_http_status' => 'select',
            ],
            'general_store_information' => [
                'general_store_information_name' => 'text',
                'general_store_information_phone' => 'text',
                'general_store_information_merchant_country' => 'select',
                'general_store_information_merchant_vat_number' => 'text',
                'general_store_information_validate_vat_number' => 'text',
                'general_store_information_address' => 'textarea',
            ],
            'general_single_store_mode' => ['general_single_store_mode_enabled' => 'select'],
        ];
        $elements = $block->getForm()->getElements();
        foreach ($elements as $element) {
            /** @var $element \Magento\Framework\Data\Form\Element\Fieldset */
            $this->assertInstanceOf('Magento\Framework\Data\Form\Element\Fieldset', $element);
            $this->assertArrayHasKey($element->getId(), $expectedIds);
            $fields = $element->getElements();
            $this->assertEquals(count($expectedIds[$element->getId()]), count($fields));
            foreach ($element->getElements() as $field) {
                $this->assertArrayHasKey($field->getId(), $expectedIds[$element->getId()]);
                $this->assertEquals($expectedIds[$element->getId()][$field->getId()], $field->getType());
            }
        }
    }
}
