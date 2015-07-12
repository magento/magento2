<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Email\Model\Template|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var \Zend_Mail|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_mail;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    protected function _mockModel($filesystem = null)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        if (!$filesystem) {
            $filesystem = $objectManager->create('Magento\Framework\Filesystem');
        }

        $this->_mail = $this->getMock(
            'Zend_Mail',
            ['send', 'addTo', 'addBcc', 'setReturnPath', 'setReplyTo'],
            ['utf-8']
        );
        $this->_model = $this->getMockBuilder(
            'Magento\Email\Model\Template'
        )->setMethods(
            ['_getMail']
        )->setConstructorArgs(
            [
                $objectManager->get('Magento\Framework\Model\Context'),
                $objectManager->get('Magento\Framework\View\DesignInterface'),
                $objectManager->get('Magento\Framework\Registry'),
                $objectManager->get('Magento\Store\Model\App\Emulation'),
                $objectManager->get('Magento\Store\Model\StoreManager'),
                $objectManager->create('Magento\Framework\View\Asset\Repository'),
                $filesystem,
                $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface'),
                $objectManager->get('Magento\Email\Model\Template\Config'),
                $objectManager->get('Magento\Email\Model\TemplateFactory'),
                $objectManager->get('Magento\Email\Model\Template\FilterFactory'),
            ]
        )->getMock();
        $objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
        $this->_model->expects($this->any())->method('_getMail')->will($this->returnCallback([$this, 'getMail']));
        $this->_model->setSenderName('sender')->setSenderEmail('sender@example.com')->setTemplateSubject('Subject');
    }

    /**
     * Manually set a module directory to allow for testing the loading of email templates from module directories.
     * TODO: Improve this method of mocking module folders, as it is fragile/error-prone
     *
     * @return \Magento\Framework\Filesystem
     */
    protected function _getMockedFilesystem($vendor = 'Magento', $module = 'Email', $type = 'view')
    {
        /* @var $moduleReader \Magento\Framework\Module\Dir\Reader */
        $moduleReader = $this->_objectManager->get('Magento\Framework\Module\Dir\Reader');
        $moduleReader->setModuleDir(
            $vendor . '_' . $module,
            $type,
            realpath(__DIR__) . "/_files/code/$vendor/$module/$type"
        );
        $directoryList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Filesystem\DirectoryList',
            [
                'root' => DirectoryList::ROOT,
                'config' => [
                    DirectoryList::MODULES => [
                        DirectoryList::PATH => realpath(__DIR__) . '/_files/code',
                    ],
                ],
            ]
        );
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Filesystem',
            ['directoryList' => $directoryList]
        );
        return $filesystem;
    }

    /**
     * Return a disposable \Zend_Mail instance
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Zend_Mail
     */
    public function getMail()
    {
        return clone $this->_mail;
    }

    public function testSetGetTemplateFilter()
    {
        $this->_mockModel();
        $filter = $this->_model->getTemplateFilter();
        $this->assertSame($filter, $this->_model->getTemplateFilter());
        $this->assertEquals(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Store\Model\StoreManagerInterface'
            )->getStore()->getId(),
            $filter->getStoreId()
        );

        $filter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Email\Model\Template\Filter'
        );
        $this->_model->setTemplateFilter($filter);
        $this->assertSame($filter, $this->_model->getTemplateFilter());
    }

    public function testLoadDefault()
    {
        $this->_mockModel();
        $this->_model->loadDefault('customer_create_account_email_template');
        $this->assertNotEmpty($this->_model->getTemplateText());
        $this->assertNotEmpty($this->_model->getTemplateSubject());
        $this->assertNotEmpty($this->_model->getOrigTemplateVariables());
        $this->assertInternalType('array', \Zend_Json::decode($this->_model->getOrigTemplateVariables()));
        $this->assertNotEmpty($this->_model->getTemplateStyles());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testGetProcessedTemplate()
    {
        $this->_mockModel();
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\AreaList'
        )->getArea(
            \Magento\Framework\App\Area::AREA_FRONTEND
        )->load();
        $this->_setNotDefaultThemeForFixtureStore();
        $expectedViewUrl = 'static/frontend/Magento/luma/en_US/Magento_Theme/favicon.ico';
        $this->_model->setTemplateText('{{view url="Magento_Theme::favicon.ico"}}');
        $this->assertStringEndsNotWith($expectedViewUrl, $this->_model->getProcessedTemplate());
        $this->_model->setDesignConfig(
            [
                'area' => 'frontend',
                'store' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    'Magento\Store\Model\StoreManagerInterface'
                )->getStore(
                    'fixturestore'
                )->getId(),
            ]
        );
        $this->assertStringEndsWith($expectedViewUrl, $this->_model->getProcessedTemplate());
    }

    /**
     * Test template directive to ensure that templates can be loaded from modules, overridden in backend, and
     * overridden in themes
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Email/Model/_files/design/themes.php
     * @magentoAppIsolation enabled
     * @dataProvider templateDirectiveDataProvider
     *
     * @param $area
     * @param $templateText
     * @param $expectedOutput
     * @param $storeConfigPath
     */
    public function testTemplateDirective($area, $templateText, $expectedOutput, $storeConfigPath = null)
    {
        $filesystem = $this->_getMockedFilesystem();
        $this->_mockModel($filesystem);
        $this->setUpThemeFallback($area);

        $this->_model->setTemplateText($templateText);

        // Allows for testing of templates overridden in backend
        if ($storeConfigPath) {
            $template = $this->_objectManager->create('Magento\Email\Model\Template');
            $templateData = [
                'template_code' => 'some_unique_code',
                'template_type' => \Magento\Email\Model\Template::TYPE_HTML,
                'template_text' => $expectedOutput,
            ];
            $template->setData($templateData);
            $template->save();
            $templateId = $template->getId();

            // Store the ID of the newly created template in the system config so that this template will be loaded
            $this->_objectManager->get(
                'Magento\Framework\App\Config\MutableScopeConfigInterface'
            )->setValue(
                $storeConfigPath,
                $templateId,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                'fixturestore'
            );
        }

        $this->assertContains($expectedOutput, $this->_model->getProcessedTemplate());
    }

    /**
     * @return array
     */
    public function templateDirectiveDataProvider()
    {
        return [
            'Template from module folder - adminhtml' => [
                \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                '{{template config_path="design/email/footer_template"}}',
                'Footer from module directory',
            ],
            'Template from module folder - frontend' => [
                \Magento\Framework\App\Area::AREA_FRONTEND,
                '{{template config_path="design/email/footer_template"}}',
                'Footer from module directory',
            ],
            'Template overridden in backend - adminhtml' => [
                \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                '{{template config_path="design/email/footer_template"}}',
                'Footer configured in backend - email loaded via adminhtml',
                'design/email/footer_template',
            ],
            'Template overridden in backend - frontend' => [
                \Magento\Framework\App\Area::AREA_FRONTEND,
                '{{template config_path="design/email/footer_template"}}',
                'Footer configured in backend - email loaded via frontend',
                'design/email/footer_template',
            ],
        ];
    }

    /**
     * Ensure that the template_styles variable contains styles from either <!--@styles @--> or the "Template Styles"
     * textarea in backend, depending on whether template was loaded from filesystem or DB.
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Email/Model/_files/design/themes.php
     * @magentoAppIsolation enabled
     * @dataProvider templateStylesVariableDataProvider
     *
     * @param string $area
     * @param string $expectedOutput
     * @param array $unexpectedOutputs
     * @param array $templateForDatabase
     */
    public function testTemplateStylesVariable($area, $expectedOutput, $unexpectedOutputs, $templateForDatabase = [])
    {
        $this->_mockModel($this->_getMockedFilesystem());
        $this->setUpThemeFallback($area);

        if (count($templateForDatabase)) {
            $template = $this->_objectManager->create('Magento\Email\Model\Template');
            $template->setData($templateForDatabase);
            $template->save();
            $templateId = $template->getId();

            $this->_model->load($templateId);
        } else {
            // <!--@styles @--> parsing only happens when template is loaded from filesystem
            $this->_model->loadDefault('design_email_header_template');
        }

        $processedTemplate = $this->_model->getProcessedTemplate();

        foreach ($unexpectedOutputs as $unexpectedOutput) {
            $this->assertNotContains($unexpectedOutput, $processedTemplate);
        }

        $this->assertContains($expectedOutput, $processedTemplate);
    }

    /**
     * @return array
     */
    public function templateStylesVariableDataProvider()
    {
        return [
            'Styles from <!--@styles @--> comment - adminhtml' => [
                \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                'p { color: #111; }',
                [
                    '<!--@styles',
                    '{{var template_styles}}',
                ],
            ],
            'Styles from <!--@styles @--> comment - frontend' => [
                \Magento\Framework\App\Area::AREA_FRONTEND,
                'p { color: #111; }',
                [
                    '<!--@styles',
                    '{{var template_styles}}',
                ],
            ],
            'Styles from "Template Styles" textarea from backend - adminhtml' => [
                \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                'p { color: #222; }',
                ['{{var template_styles}}'],
                [
                    'template_code' => 'some_unique_code',
                    'template_type' => \Magento\Email\Model\Template::TYPE_HTML,
                    'template_text' => 'Footer from database {{var template_styles}}',
                    'template_styles' => 'p { color: #222; }',
                ],
            ],
            'Styles from "Template Styles" textarea from backend - frontend' => [
                \Magento\Framework\App\Area::AREA_FRONTEND,
                'p { color: #333; }',
                ['{{var template_styles}}'],
                [
                    'template_code' => 'some_unique_code',
                    'template_type' => \Magento\Email\Model\Template::TYPE_HTML,
                    'template_text' => 'Footer from database {{var template_styles}}',
                    'template_styles' => 'p { color: #333; }',
                ],
            ],
        ];
    }

    /**
     * Setup the theme fallback structure and set the Vendor/custom_theme as the current theme for 'fixturestore' store
     *
     * @param $area
     */
    protected function setUpThemeFallback($area)
    {
        $themes = ['frontend' => 'Vendor/custom_theme'];
        $design = $this->_objectManager->create('Magento\Theme\Model\View\Design', ['themes' => $themes]);
        $this->_objectManager->addSharedInstance($design, 'Magento\Theme\Model\View\Design');

        // It is important to test from both areas, as emails will get sent from both, so we need to ensure that the
        // inline CSS files get loaded properly from both areas.
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea($area);

        $collection = $this->_objectManager->create('Magento\Theme\Model\Resource\Theme\Collection');

        // Hard-coding theme as we want to test the fallback structure to ensure that the parent/grandparent themes of
        // Vendor/custom_theme will be checked for CSS files
        $themeId = $collection->getThemeByFullPath('frontend/Vendor/custom_theme')->getId();

        $this->_objectManager->get(
            'Magento\Framework\App\Config\MutableScopeConfigInterface'
        )->setValue(
            \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
            $themeId,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            'fixturestore'
        );

        $this->_model->setDesignConfig(
            [
                'area' => 'frontend',
                'store' => $this->_objectManager->get(
                    'Magento\Store\Model\StoreManagerInterface'
                )->getStore(
                    'fixturestore'
                )->getId(),
            ]
        );
    }

    /**
     * Set 'Magento/luma' for the 'fixturestore' store.
     * Application isolation is required, if a test uses this method.
     */
    protected function _setNotDefaultThemeForFixtureStore()
    {
        $theme = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Design\ThemeInterface'
        );
        $theme->load('Magento/luma', 'theme_path');
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\Config\MutableScopeConfigInterface'
        )->setValue(
            \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
            $theme->getId(),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            'fixturestore'
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testGetProcessedTemplateSubject()
    {
        $this->_mockModel();
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\AreaList'
        )->getArea(
            \Magento\Framework\App\Area::AREA_FRONTEND
        )->load();
        $this->_setNotDefaultThemeForFixtureStore();
        $expectedViewUrl = 'static/frontend/Magento/luma/en_US/Magento_Theme/favicon.ico';
        $this->_model->setTemplateSubject('{{view url="Magento_Theme::favicon.ico"}}');
        $this->assertStringEndsNotWith($expectedViewUrl, $this->_model->getProcessedTemplateSubject([]));
        $this->_model->setDesignConfig(
            [
                'area' => 'frontend',
                'store' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    'Magento\Store\Model\StoreManagerInterface'
                )->getStore(
                    'fixturestore'
                )->getId(),
            ]
        );
        $this->assertStringEndsWith($expectedViewUrl, $this->_model->getProcessedTemplateSubject([]));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetDefaultEmailLogo()
    {
        $this->_mockModel();
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\AreaList'
        )->getArea(
            \Magento\Framework\App\Area::AREA_FRONTEND
        )->load();
        $this->assertStringEndsWith(
            'static/frontend/Magento/blank/en_US/Magento_Email/logo_email.png',
            $this->_model->getDefaultEmailLogo()
        );
    }

    /**
     * @dataProvider setDesignConfigExceptionDataProvider
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testSetDesignConfigException($config)
    {
        $this->_mockModel();
        // \Magento\Email\Model\Template is an abstract class
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Email\Model\Template');
        $model->setDesignConfig($config);
    }

    public function setDesignConfigExceptionDataProvider()
    {
        $this->_mockModel();
        $storeId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        return [
            [[]],
            [['area' => 'frontend']],
            [['store' => $storeId]],
        ];
    }

    public function testSetAndGetId()
    {
        $this->_mockModel();
        $testId = 9999;
        $this->_model->setId($testId);
        $this->assertEquals($testId, $this->_model->getId());
    }

    public function testIsValidForSend()
    {
        $this->_mockModel();
        $this->assertTrue($this->_model->isValidForSend());
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Email template 'foo' is not defined.
     */
    public function testGetTypeNonExistentType()
    {
        $this->_mockModel();
        $this->_model->setId('foo');
        $this->_model->getType();
    }

    public function testGetTypeHtml()
    {
        $this->_mockModel();
        $this->_model->setId('customer_create_account_email_template');
        $this->assertEquals(\Magento\Framework\App\TemplateTypesInterface::TYPE_HTML, $this->_model->getType());
    }

    public function testGetType()
    {
        $this->_mockModel();
        $templateTypeId = 'test_template';
        $this->_model->setTemplateType($templateTypeId);
        $this->assertEquals($templateTypeId, $this->_model->getType());
    }

    public function testGetSendingException()
    {
        $this->_mockModel();
        $this->assertNull($this->_model->getSendingException());
    }

    public function testGetVariablesOptionArray()
    {
        $this->_mockModel();
        $testTemplateVariables = '{"var data.name":"Sender Name","var data.email":"Sender Email"}';
        $this->_model->setOrigTemplateVariables($testTemplateVariables);
        $variablesOptionArray = $this->_model->getVariablesOptionArray();
        $this->assertEquals('{{var data.name}}', $variablesOptionArray[0]['value']);
        $this->assertEquals('Sender Name', $variablesOptionArray[0]['label']->getArguments()[0]);
        $this->assertEquals('{{var data.email}}', $variablesOptionArray[1]['value']);
        $this->assertEquals('Sender Email', $variablesOptionArray[1]['label']->getArguments()[0]);
    }

    public function testGetVariablesOptionArrayInGroup()
    {
        $this->_mockModel();
        $testTemplateVariables = '{"var data.name":"Sender Name","var data.email":"Sender Email"}';
        $this->_model->setOrigTemplateVariables($testTemplateVariables);
        $variablesOptionArray = $this->_model->getVariablesOptionArray(true);
        $this->assertEquals('Template Variables', $variablesOptionArray['label']->getText());
        $this->assertEquals($this->_model->getVariablesOptionArray(), $variablesOptionArray['value']);
    }

    /**
     * @expectedException \Magento\Framework\Exception\MailException
     * @expectedExceptionMessage Please enter a template name.
     */
    public function testBeforeSaveEmptyTemplateCode()
    {
        $this->_mockModel();
        $this->_model->beforeSave();
    }

    public function testBeforeSave()
    {
        $this->_mockModel();
        $this->_model->setTemplateCode('test template code');
        $this->_model->beforeSave();
    }

    public function testProcessTemplate()
    {
        $this->_mockModel();
        $this->_model->setId('customer_create_account_email_template');
        $this->assertContains('<body style', $this->_model->processTemplate());
    }

    public function testGetSubject()
    {
        $this->_mockModel();
        $this->_model->setVars(['foo', 'bar', 'baz']);
        $this->assertEquals('Subject', $this->_model->getSubject());
    }

    public function testSetOptions()
    {
        $this->_mockModel();
        $options = ['area' => 'test area', 'store' => 1];
        $this->_model->setOptions($options);
        $this->assertEquals($options, $this->_model->getDesignConfig()->getData());
    }
}
