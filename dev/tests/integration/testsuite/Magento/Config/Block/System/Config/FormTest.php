<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Block\System\Config;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\Cache\State;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\View\Element\Text;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;

/**
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    private $formFactory;

    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Section
     */
    private $section;

    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Group
     */
    private $group;

    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Field
     */
    private $field;

    /**
     * @var array
     */
    private $configData;

    /** @var string Encrypted value stored in config.xml */
    private static $defaultConfigEncrypted = 'Encrypted value';

    /** @var array Serialized value stored in config.xml  */
    private static $defaultConfigSerialized = ['value1', 'value2'];

    /** @var string Serialized value stored in config.xml  */
    private static $defaultConfigString = 'test config value';

    /** @var string Encrypted value stored in DB */
    private static $websiteDbEncrypted = 'DB encrypted value';

    /** @var array Serialized value stored in DB */
    private static $websiteDbSerialized = ['value3', 'value4'];

    /** @var string String value stored in DB */
    private static $websiteDBString = 'test db value';

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->formFactory = $this->objectManager->create(\Magento\Framework\Data\FormFactory::class);
    }

    public function testDependenceHtml()
    {
        /** @var $layout \Magento\Framework\View\LayoutInterface */
        $layout = Bootstrap::getObjectManager()->create(
            \Magento\Framework\View\Layout::class,
            ['area' => 'adminhtml']
        );
        Bootstrap::getObjectManager()->get(
            ScopeInterface::class
        )->setCurrentScope(
            FrontNameResolver::AREA_CODE
        );
        /** @var $block Form */
        $block = $layout->createBlock(Form::class, 'block');

        /** @var $childBlock Text */
        $childBlock = $layout->addBlock(Text::class, 'element_dependence', 'block');

        $expectedValue = 'dependence_html_relations';
        $this->assertStringNotContainsString($expectedValue, $block->toHtml());

        $childBlock->setText($expectedValue);
        $this->assertStringContainsString($expectedValue, $block->toHtml());
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

        Bootstrap::getObjectManager()->get(
            ScopeInterface::class
        )->setCurrentScope(
            FrontNameResolver::AREA_CODE
        );
        $form = $this->formFactory->create();
        $fieldset = $form->addFieldset($this->section->getId() . '_' . $this->group->getId(), []);

        /* @TODO Eliminate stub by proper mock / config fixture usage */
        /** @var $block FormStub */
        $block = Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            FormStub::class
        );
        $block->setScope(Form::SCOPE_WEBSITES);
        $block->setStubConfigData($this->configData);
        $block->initFields($fieldset, $this->group, $this->section);

        $valueSel = sprintf(
            '//input[@id="%s_%s_%s"]',
            $this->section->getId(),
            $this->group->getId(),
            $this->field->getId()
        );
        $valueDisabledSel = sprintf('%s[@disabled="disabled"]', $valueSel);
        $useDefaultSel = sprintf(
            '//input[@id="%s_%s_%s_inherit" and contains(@class,"checkbox")]',
            $this->section->getId(),
            $this->group->getId(),
            $this->field->getId()
        );
        $useDefaultCheckedSel = sprintf('%s[@checked="checked"]', $useDefaultSel);
        $fieldsetHtml = $fieldset->getElementHtml();
        $this->assertGreaterThanOrEqual(
            1,
            Xpath::getElementsCountForXpath('//fieldset', $fieldsetHtml),
            'Fieldset HTML is invalid'
        );
        $this->assertEquals(
            $valueSelCtr,
            Xpath::getElementsCountForXpath($valueSel, $fieldsetHtml),
            'Field input should appear ' . $valueSelCtr . ' times in fieldset HTML'
        );
        $this->assertEquals(
            $valueSelCtr,
            Xpath::getElementsCountForXpath($useDefaultSel, $fieldsetHtml),
            '"Use Default" checkbox should appear' . $valueSelCtr . ' times  in fieldset HTML.'
        );

        if ($expectedUseDefault) {
            $this->assertGreaterThanOrEqual(
                1,
                Xpath::getElementsCountForXpath($useDefaultCheckedSel, $fieldsetHtml),
                '"Use Default" checkbox should be checked'
            );
            $this->assertGreaterThanOrEqual(
                1,
                Xpath::getElementsCountForXpath($valueDisabledSel, $fieldsetHtml),
                'Field input should be disabled'
            );
        } else {
            $this->assertEquals(
                0,
                Xpath::getElementsCountForXpath($useDefaultCheckedSel, $fieldsetHtml),
                '"Use Default" checkbox should not be checked'
            );
            $this->assertEquals(
                0,
                Xpath::getElementsCountForXpath($valueDisabledSel, $fieldsetHtml),
                'Field input should not be disabled'
            );
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

        Bootstrap::getObjectManager()->get(
            ScopeInterface::class
        )->setCurrentScope(
            FrontNameResolver::AREA_CODE
        );
        $form = $this->formFactory->create();
        $fieldset = $form->addFieldset($this->section->getId() . '_' . $this->group->getId(), []);

        /* @TODO Eliminate stub by proper mock / config fixture usage */
        /** @var $block FormStub */
        $block = Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            FormStub::class
        );
        $block->setScope(Form::SCOPE_DEFAULT);
        $block->setStubConfigData($this->configData);
        $block->initFields($fieldset, $this->group, $this->section);

        $valueSel = sprintf(
            '//input[@id="%s_%s_%s"]',
            $this->section->getId(),
            $this->group->getId(),
            $this->field->getId()
        );
        $fieldsetHtml = $fieldset->getElementHtml();

        $this->assertGreaterThanOrEqual(
            1,
            Xpath::getElementsCountForXpath(
                '//fieldset',
                $fieldsetHtml
            ),
            'Fieldset HTML is invalid'
        );

        $this->assertEquals(
            $valueSelCtr,
            Xpath::getElementsCountForXpath(
                $valueSel,
                $fieldsetHtml
            ),
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
     * Test cases with retrieving config data with backend models for different scopes.
     * Config data are stored in config.xml and database.
     *
     * @param string $fieldId
     * @param string $expectedConfigValue
     * @param string $currentScope
     * @param string $currentScopeCode
     * @param bool $isDbOverrideValue whether values should be overridden in the database for the current scope
     *
     * @dataProvider initFieldsWithBackendModelDataProvider
     * @covers \Magento\Config\Block\System\Config\Form::initFields
     */
    public function testInitFieldsWithBackendModel(
        $fieldId,
        $expectedConfigValue,
        $currentScope,
        $currentScopeCode,
        $isDbOverrideValue
    ) {
        $this->_setupFieldsInheritCheckbox($fieldId, false, $expectedConfigValue);

        if ($isDbOverrideValue) {
            $backendModel = $this->field->getAttribute('backend_model') ?: \Magento\Framework\App\Config\Value::class;
            $path = $this->section->getId() . '/' . $this->group->getId() . '/' . $this->field->getId();
            $model = Bootstrap::getObjectManager()->create($backendModel);
            $model->setPath($path);
            $model->setScopeId($currentScopeCode);
            $model->setScope($currentScope);
            $model->setScopeCode($currentScopeCode);
            $model->setValue($expectedConfigValue);
            $model->save();
        }

        $this->registerTestConfigXmlMetadata();
        Bootstrap::getObjectManager()->get(\Magento\TestFramework\App\Config::class)->clean();

        $form = $this->formFactory->create();
        $fieldset = $form->addFieldset($this->section->getId() . '_' . $this->group->getId(), []);

        /** @var $block FormStub */
        $block = Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            FormStub::class
        );

        $block->setScope($currentScope);
        $block->setScopeCode($currentScopeCode);
        $block->setStubConfigData($this->configData);
        $block->initFields($fieldset, $this->group, $this->section);

        $fieldsetHtml = $fieldset->getElementHtml();

        $elementId = $this->section->getId() . '_' . $this->group->getId() . '_' . $this->field->getId();
        if (is_array($expectedConfigValue)) {
            $expectedConfigValue = implode('|', $expectedConfigValue);
        }
        $this->assertEquals(
            $expectedConfigValue,
            $this->getElementAttributeValueById($fieldsetHtml, $elementId)
        );
    }

    /**
     * Provides config data variations with different data types and scopes.
     *
     * @return array
     */
    public static function initFieldsWithBackendModelDataProvider()
    {
        return [
            /** Values stored in config.xml only for default scope, then retrieved for default scope. */
            ['test_field_encrypted', self::$defaultConfigEncrypted, FormStub::SCOPE_DEFAULT, 0, false],
            ['test_field_serialized', self::$defaultConfigSerialized, FormStub::SCOPE_DEFAULT, 0, false],
            ['test_field', self::$defaultConfigString, FormStub::SCOPE_DEFAULT, 0, false],

            /** Values stored in config.xml only for default scope, then retrieved for website scope. */
            ['test_field_encrypted', self::$defaultConfigEncrypted, FormStub::SCOPE_WEBSITES, 1, false],
            ['test_field_serialized', self::$defaultConfigSerialized, FormStub::SCOPE_WEBSITES, 1, false],
            ['test_field', self::$defaultConfigString, FormStub::SCOPE_WEBSITES, 1, false],

            /**
             * Values stored in config.xml for default scope and in database for website scope,
             * then retrieved for website scope.
             */
            ['test_field_encrypted', self::$websiteDbEncrypted, FormStub::SCOPE_WEBSITES, 1, true],
            ['test_field_serialized', self::$websiteDbSerialized, FormStub::SCOPE_WEBSITES, 1, true],
            ['test_field', self::$websiteDBString, FormStub::SCOPE_WEBSITES, 1, true],
        ];
    }

    /**
     * @param string $fieldId uses the test_field_use_config field if true
     * @param bool $isConfigDataEmpty if the config data array should be empty or not
     * @param string $configDataValue the value that the field path should be set to in the config data
     */
    protected function _setupFieldsInheritCheckbox($fieldId, $isConfigDataEmpty, $configDataValue)
    {
        Bootstrap::getInstance()->reinitialize([
            State::PARAM_BAN_CACHE => true,
        ]);
        Bootstrap::getObjectManager()
            ->get(ScopeInterface::class)
            ->setCurrentScope(FrontNameResolver::AREA_CODE);
        Bootstrap::getObjectManager()->get(\Magento\Framework\App\AreaList::class)
            ->getArea(FrontNameResolver::AREA_CODE)
            ->load(\Magento\Framework\App\Area::PART_CONFIG);

        $fileResolverMock = $this->getMockBuilder(
            \Magento\Framework\App\Config\FileResolver::class
        )->disableOriginalConstructor()->getMock();
        $fileIteratorFactory = Bootstrap::getObjectManager()->get(
            \Magento\Framework\Config\FileIteratorFactory::class
        );
        $fileIterator = $fileIteratorFactory->create(
            [__DIR__ . '/_files/test_system.xml']
        );
        $fileResolverMock->expects($this->any())->method('get')->willReturn($fileIterator);

        $objectManager = Bootstrap::getObjectManager();

        $structureReader = $objectManager->create(
            \Magento\Config\Model\Config\Structure\Reader::class,
            ['fileResolver' => $fileResolverMock]
        );
        $structureData = $objectManager->create(
            \Magento\Config\Model\Config\Structure\Data::class,
            ['reader' => $structureReader]
        );
        /** @var \Magento\Config\Model\Config\Structure $structure  */
        $structure = $objectManager->create(
            \Magento\Config\Model\Config\Structure::class,
            ['structureData' => $structureData]
        );

        $this->section = $structure->getElement('test_section');

        $this->group = $structure->getElement('test_section/test_group');

        $this->field = $structure->getElement('test_section/test_group/' . $fieldId);

        $fieldPath = $this->field->getConfigPath();

        if ($isConfigDataEmpty) {
            $this->configData = [];
        } else {
            $this->configData = [$fieldPath => $configDataValue];
        }
    }

    public function testInitFormAddsFieldsets()
    {
        Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\ResponseInterface::class
        )->headersSentThrowsException = false;
        Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\RequestInterface::class
        )->setParam(
            'section',
            'general'
        );
        /** @var $block Form */
        $block = Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            Form::class
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
            $this->assertInstanceOf(
                \Magento\Framework\Data\Form\Element\Fieldset::class,
                $element
            );
            $this->assertArrayHasKey($element->getId(), $expectedIds);
            $fields = $element->getElements();
            $this->assertEquals(count($expectedIds[$element->getId()]), count($fields));
            foreach ($element->getElements() as $field) {
                $this->assertArrayHasKey($field->getId(), $expectedIds[$element->getId()]);
                $this->assertEquals($expectedIds[$element->getId()][$field->getId()], $field->getType());
            }
        }
    }

    /**
     * Add metadata from test_config.xml to metadataConfigTypeProcessor.
     */
    private function registerTestConfigXmlMetadata()
    {
        /** @var \Magento\Framework\Encryption\EncryptorInterface $encryptor */
        $encryptor = Bootstrap::getObjectManager()->get(\Magento\Framework\Encryption\EncryptorInterface::class);
        $this->setEncryptedValue(
            $encryptor->encrypt(self::$defaultConfigEncrypted)
        );

        $fileResolver = Bootstrap::getObjectManager()->create(FileResolverInterface::class);
        $directories = $fileResolver->get('config.xml', 'global');

        $property = new \ReflectionProperty($directories, 'paths');
        $property->setAccessible(true);
        $property->setValue(
            $directories,
            array_merge($property->getValue($directories), [__DIR__ . '/_files/test_config.xml'])
        );

        $fileResolverMock = $this->getMockForAbstractClass(FileResolverInterface::class);
        $fileResolverMock->method('get')->willReturn($directories);

        $initialReader = Bootstrap::getObjectManager()->create(
            \Magento\Framework\App\Config\Initial\Reader::class,
            ['fileResolver' => $fileResolverMock]
        );

        $initialConfig = Bootstrap::getObjectManager()->create(
            \Magento\Framework\App\Config\Initial::class,
            ['reader' => $initialReader]
        );
        $metadataConfigTypeProcessor = Bootstrap::getObjectManager()->create(
            \Magento\Framework\App\Config\MetadataConfigTypeProcessor::class,
            ['initialConfig' => $initialConfig]
        );

        $composite = Bootstrap::getObjectManager()->get('systemConfigPostProcessorComposite');
        $property = new \ReflectionProperty($composite, 'processors');
        $property->setAccessible(true);
        $processors = $property->getValue($composite);
        $processors['metadata'] = $metadataConfigTypeProcessor;
        $property->setValue($composite, $processors);

        Bootstrap::getObjectManager()->get(\Magento\TestFramework\App\State::class)
            ->setAreaCode(FrontNameResolver::AREA_CODE);
    }

    /**
     * Finds element by id and returns value of attribute value property.
     *
     * @param string $html
     * @param string $nodeId
     *
     * @return string
     */
    private function getElementAttributeValueById($html, $nodeId)
    {
        $domDocument = new \DOMDocument();
        libxml_use_internal_errors(true);
        $domDocument->loadHTML($html);
        libxml_use_internal_errors(false);
        $element = $domDocument->getElementById($nodeId);

        return $element ? $element->attributes->getNamedItem('value')->nodeValue : '';
    }

    /**
     * Save encrypted value to test_config.xml
     *
     * @param string $encryptedValue
     */
    private function setEncryptedValue($encryptedValue)
    {
        $config = simplexml_load_file(__DIR__ . '/_files/test_config.xml');
        $config->default->test_section->test_group->test_field_encrypted = $encryptedValue;
        $config->asXml(__DIR__ . '/_files/test_config.xml');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $reflection = new \ReflectionObject($this);
        foreach ($reflection->getProperties() as $property) {
            if (!$property->isStatic() && 0 !== strpos($property->getDeclaringClass()->getName(), 'PHPUnit')) {
                $property->setAccessible(true);
                $property->setValue($this, null);
            }
        }
        $this->setEncryptedValue('{ENCRYPTED_VALUE}');

        $configResourceModel = Bootstrap::getObjectManager()->get(\Magento\Config\Model\ResourceModel\Config::class);
        foreach (['test_field_encrypted', 'test_field_serialized', 'test_field'] as $field) {
            $path = 'test_section/test_group/' . $field;
            $configResourceModel->deleteConfig($path, FormStub::SCOPE_WEBSITES, 1);
        }

        parent::tearDown();
    }
}
