<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Email\Model\Template|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Zend_Mail|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mail;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    protected function mockModel($filesystem = null)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        if (!$filesystem) {
            $filesystem = $objectManager->create('Magento\Framework\Filesystem');
        }

        $this->mail = $this->getMock(
            'Zend_Mail',
            ['send', 'addTo', 'addBcc', 'setReturnPath', 'setReplyTo'],
            ['utf-8']
        );
        $this->model = $this->getMockBuilder(
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
                $filesystem,
                $objectManager->create('Magento\Framework\View\Asset\Repository'),
                $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface'),
                $objectManager->get('Magento\Framework\ObjectManagerInterface'),
                $objectManager->get('Magento\Email\Model\Template\Config'),
                $objectManager->get('Magento\Email\Model\Template\FilterFactory'),
                $objectManager->get('Magento\Email\Model\TemplateFactory')
            ]
        )->getMock();
        $objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
        $this->model->expects($this->any())->method('_getMail')->will($this->returnCallback([$this, 'getMail']));
        $this->model->setSenderName('sender')->setSenderEmail('sender@example.com')->setTemplateSubject('Subject');
    }

    /**
     * Manually set a module directory to allow for testing the loading of email templates from module directories.
     * TODO: Improve this method of mocking module folders, as it is fragile/error-prone
     *
     * @return \Magento\Framework\Filesystem
     */
    protected function getMockedFilesystem($vendor = 'Magento', $module = 'Email', $type = 'view', $area = 'frontend')
    {

        //TODO - add area into path -- need to test both frontend and adminhtml

        /* @var $moduleReader \Magento\Framework\Module\Dir\Reader */
        $moduleReader = $this->objectManager->get('Magento\Framework\Module\Dir\Reader');
        $moduleReader->setModuleDir(
            $vendor . '_' . $module,
            $type,
            realpath(__DIR__) . "/_files/code/$vendor/$module/$type/$area"
        );

        $directoryList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Filesystem\DirectoryList',
            [
                'root' => BP,
                'config' => [
                    DirectoryList::MODULES => [
                        DirectoryList::PATH => realpath(__DIR__) . '/_files/code'
                    ],
                ]
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
        return clone $this->mail;
    }

    public function testSetGetTemplateFilter()
    {
        $this->mockModel();
        $filter = $this->model->getTemplateFilter();
        $this->assertSame($filter, $this->model->getTemplateFilter());
        $this->assertEquals(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Store\Model\StoreManagerInterface'
            )->getStore()->getId(),
            $filter->getStoreId()
        );

        $filter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Email\Model\Template\Filter'
        );
        $this->model->setTemplateFilter($filter);
        $this->assertSame($filter, $this->model->getTemplateFilter());
    }

    public function testLoadDefault()
    {
        $this->mockModel();
        $this->model->loadDefault('customer_create_account_email_template');
        $this->assertNotEmpty($this->model->getTemplateText());
        $this->assertNotEmpty($this->model->getTemplateSubject());
        $this->assertNotEmpty($this->model->getOrigTemplateVariables());
        $this->assertInternalType('array', \Zend_Json::decode($this->model->getOrigTemplateVariables()));
        $this->assertNotEmpty($this->model->getTemplateStyles());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testGetProcessedTemplate()
    {
        $this->mockModel();
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\AreaList'
        )->getArea(
            \Magento\Framework\App\Area::AREA_FRONTEND
        )->load();
        $this->setNotDefaultThemeForFixtureStore();
        $expectedViewUrl = 'static/frontend/Magento/luma/en_US/Magento_Theme/favicon.ico';
        $this->model->setTemplateText('{{view url="Magento_Theme::favicon.ico"}}');
        $this->assertStringEndsNotWith($expectedViewUrl, $this->model->getProcessedTemplate());
        $this->model->setDesignConfig(
            [
                'area' => 'frontend',
                'store' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    'Magento\Store\Model\StoreManagerInterface'
                )->getStore(
                    'fixturestore'
                )->getId()
            ]
        );
        $this->assertStringEndsWith($expectedViewUrl, $this->model->getProcessedTemplate());
    }

    /**
     * Ensures that the css directive will successfully compile and output contents of a LESS file,
     * as well as supporting loading files from a theme fallback structure. The css directive must be tested within
     * the context of a Magento\Email\Model\AbstractTemplate class, as it uses its methods to load the CSS.
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Email/Model/_files/design/themes.php
     * @magentoAppIsolation enabled
     * @dataProvider cssDirectiveDataProvider
     *
     * @param $area
     * @param $templateText
     * @param $expectedOutput
     */
    public function testCssDirective($area, $templateText, $expectedOutput)
    {
        $this->mockModel();
        $this->setUpThemeFallback($area);
        $this->model->setTemplateText($templateText);

        $this->assertContains($expectedOutput, $this->model->getProcessedTemplate());
    }

    /**
     * @return array
     */
    public function cssDirectiveDataProvider()
    {
        return [
            'CSS from theme - adminhtml' => [
                \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                '<html>{{css file="css/email-1.css"}}</html>',
                'color: #111;'
            ],
            'CSS from theme - frontend' => [
                \Magento\Framework\App\Area::AREA_FRONTEND,
                '<html>{{css file="css/email-1.css"}}</html>',
                'color: #111;'
            ],
            'CSS from parent theme - adminhtml' => [
                \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                '<html>{{css file="css/email-2.css"}}</html>',
                'color: #222;'
            ],
            'CSS from parent theme - frontend' => [
                \Magento\Framework\App\Area::AREA_FRONTEND,
                '<html>{{css file="css/email-2.css"}}</html>',
                'color: #222;'
            ],
            'CSS from grandparent theme - adminhtml' => [
                \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                '<html>{{css file="css/email-3.css"}}</html>',
                'color: #333;'
            ],
            'CSS from grandparent theme - frontend' => [
                \Magento\Framework\App\Area::AREA_FRONTEND,
                '<html>{{css file="css/email-3.css"}}</html>',
                'color: #333;'
            ],
            'Missing file argument' => [
                \Magento\Framework\App\Area::AREA_FRONTEND,
                '<html>{{css}}</html>',
                '/* "file" argument must be specified */'
            ],
            'Empty or missing file' => [
                \Magento\Framework\App\Area::AREA_FRONTEND,
                '<html>{{css file="css/non-existent-file.css"}}</html>',
                '/* Contents of css/non-existent-file.css could not be loaded or is empty */'
            ],
        ];
    }

    /**
     * Ensures that the inlinecss directive will successfully load and inline CSS to HTML markup,
     * as well as supporting loading files from a theme fallback structure. The inlinecss directive must be tested within
     * the context of a Magento\Email\Model\AbstractTemplate class, as it uses its methods to load the CSS.
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Email/Model/_files/design/themes.php
     * @magentoAppIsolation enabled
     * @dataProvider inlinecssDirectiveDataProvider
     *
     * @param $area
     * @param $templateText
     * @param $expectedOutput
     */
    public function testInlinecssDirective($area, $templateText, $expectedOutput)
    {
        $this->mockModel();
        $this->setUpThemeFallback($area);
        $this->model->setTemplateText($templateText);

        $this->assertContains($expectedOutput, $this->model->getProcessedTemplate());
    }

    /**
     * @return array
     */
    public function inlinecssDirectiveDataProvider()
    {
        return [
            'CSS from theme - adminhtml' => [
                \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                '<html><p></p> {{inlinecss file="css/email-inline-1.css"}}</html>',
                '<p style="color: #111; text-align: left;">'
            ],
            'CSS from theme - frontend' => [
                \Magento\Framework\App\Area::AREA_FRONTEND,
                '<html><p></p> {{inlinecss file="css/email-inline-1.css"}}</html>',
                '<p style="color: #111; text-align: left;">'
            ],
            'CSS from parent theme - adminhtml' => [
                \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                '<html><p></p> {{inlinecss file="css/email-inline-2.css"}}</html>',
                '<p style="color: #222; text-align: left;">'
            ],
            'CSS from parent theme - frontend' => [
                \Magento\Framework\App\Area::AREA_FRONTEND,
                '<html><p></p> {{inlinecss file="css/email-inline-2.css"}}</html>',
                '<p style="color: #222; text-align: left;">'
            ],
            'CSS from grandparent theme - adminhtml' => [
                \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                '<html><p></p> {{inlinecss file="css/email-inline-3.css"}}</html>',
                '<p style="color: #333; text-align: left;">'
            ],
            'CSS from grandparent theme - frontend' => [
                \Magento\Framework\App\Area::AREA_FRONTEND,
                '<html><p></p> {{inlinecss file="css/email-inline-3.css"}}</html>',
                '<p style="color: #333; text-align: left;">'
            ],
            'Non-existent file results in unmodified markup' => [
                \Magento\Framework\App\Area::AREA_FRONTEND,
                '<html><p></p> {{inlinecss file="css/non-existent-file.css"}}</html>',
                '<html><p></p> </html>',
            ],
        ];
    }

    /**
     * Test template directive to ensure that templates can be loaded from modules
     *
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Email/Model/_files/design/themes.php
     * @magentoAppIsolation enabled
     * @dataProvider templateFallbackDataProvider
     *
     * @param $area
     * @param $templateId
     * @param $expectedOutput
     * @param $defaultTheme
     */
    public function testTemplateFallback($area, $templateId, $expectedOutput, $defaultTheme = true)
    {
        $this->mockModel();

        $filesystem = $this->getMockedFilesystem('Magento','Email','view', $area);
        $this->mockModel($filesystem);
        $this->setUpThemeFallback($area);

        $this->model->setId($templateId);

        if (!$defaultTheme)
        {
            $this->setNotDefaultThemeForFixtureStore();
        }

        $this->assertContains($expectedOutput, $this->model->processTemplate());
    }

    /**
     * @return array
     */
    public function templateFallbackDataProvider()
    {
        return [
            'Template from module - admin' => [
                \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                'customer_create_account_email_template',
                'If you have any questions about your account',
            ],
            'Template from module - frontend' => [
                \Magento\Framework\App\Area::AREA_FRONTEND,
                'customer_create_account_email_template',
                'If you have any questions about your account',
            ],
            'Template from theme - frontend' => [
                \Magento\Framework\App\Area::AREA_FRONTEND,
                'customer_create_account_email_template',
                'If you have any questions about your account',
                false
            ],

        ];
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
        $filesystem = $this->getMockedFilesystem('Magento','Email','view', $area);
        $this->mockModel($filesystem);
        $this->setUpThemeFallback($area);

        $this->model->setTemplateText($templateText);

        // Allows for testing of templates overridden in backend
        if ($storeConfigPath) {
            $template = $this->objectManager->create('Magento\Email\Model\Template');
            $templateData = [
                'template_code' => 'some_unique_code',
                'template_type' => \Magento\Email\Model\Template::TYPE_HTML,
                'template_text' => $expectedOutput,
            ];
            $template->setData($templateData);
            $template->save();
            $templateId = $template->getId();

            // Store the ID of the newly created template in the system config so that this template will be loaded
            $this->objectManager->get(
                'Magento\Framework\App\Config\MutableScopeConfigInterface'
            )->setValue(
                $storeConfigPath,
                $templateId,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                'fixturestore'
            );
        }

        $this->assertContains($expectedOutput, $this->model->getProcessedTemplate());
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
                '<!-- End wrapper table -->',
            ],
            'Template from module folder - frontend' => [
                \Magento\Framework\App\Area::AREA_FRONTEND,
                '{{template config_path="design/email/footer_template"}}',
                '<!-- End wrapper table -->',
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
        $this->mockModel($this->getMockedFilesystem());
        $this->setUpThemeFallback($area);

        if (count($templateForDatabase)) {
            $template = $this->objectManager->create('Magento\Email\Model\Template');
            $template->setData($templateForDatabase);
            $template->save();
            $templateId = $template->getId();

            $this->model->load($templateId);
        } else {
            // <!--@styles @--> parsing only happens when template is loaded from filesystem
            $this->model->loadDefault('design_email_header_template');
        }

        $processedTemplate = $this->model->getProcessedTemplate();

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
                ]
            ],
            'Styles from <!--@styles @--> comment - frontend' => [
                \Magento\Framework\App\Area::AREA_FRONTEND,
                'p { color: #111; }',
                [
                    '<!--@styles',
                    '{{var template_styles}}',
                ]
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
        $design = $this->objectManager->create('Magento\Theme\Model\View\Design', ['themes' => $themes]);
        $this->objectManager->addSharedInstance($design, 'Magento\Theme\Model\View\Design');

        // It is important to test from both areas, as emails will get sent from both, so we need to ensure that the
        // inline CSS files get loaded properly from both areas.
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea($area);

        $collection = $this->objectManager->create('Magento\Theme\Model\Resource\Theme\Collection');

        // Hard-coding theme as we want to test the fallback structure to ensure that the parent/grandparent themes of
        // Vendor/custom_theme will be checked for CSS files
        $themeId = $collection->getThemeByFullPath('frontend/Vendor/custom_theme')->getId();

        $this->objectManager->get(
            'Magento\Framework\App\Config\MutableScopeConfigInterface'
        )->setValue(
            \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
            $themeId,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            'fixturestore'
        );

        $this->model->setDesignConfig(
            [
                'area' => 'frontend',
                'store' => $this->objectManager->get(
                    'Magento\Store\Model\StoreManagerInterface'
                )->getStore(
                    'fixturestore'
                )->getId()
            ]
        );
    }

    /**
     * Set 'Magento/luma' for the 'fixturestore' store.
     * Application isolation is required, if a test uses this method.
     */
    protected function setNotDefaultThemeForFixtureStore()
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
        $this->mockModel();
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\AreaList'
        )->getArea(
            \Magento\Framework\App\Area::AREA_FRONTEND
        )->load();
        $this->setNotDefaultThemeForFixtureStore();
        $expectedViewUrl = 'static/frontend/Magento/luma/en_US/Magento_Theme/favicon.ico';
        $this->model->setTemplateSubject('{{view url="Magento_Theme::favicon.ico"}}');
        $this->assertStringEndsNotWith($expectedViewUrl, $this->model->getProcessedTemplateSubject([]));
        $this->model->setDesignConfig(
            [
                'area' => 'frontend',
                'store' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    'Magento\Store\Model\StoreManagerInterface'
                )->getStore(
                    'fixturestore'
                )->getId()
            ]
        );
        $this->assertStringEndsWith($expectedViewUrl, $this->model->getProcessedTemplateSubject([]));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetDefaultEmailLogo()
    {
        $this->mockModel();
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\AreaList'
        )->getArea(
            \Magento\Framework\App\Area::AREA_FRONTEND
        )->load();
        $this->assertStringEndsWith(
            'static/frontend/Magento/blank/en_US/Magento_Email/logo_email.png',
            $this->model->getDefaultEmailLogo()
        );
    }

    /**
     * @dataProvider setDesignConfigExceptionDataProvider
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testSetDesignConfigException($config)
    {
        $this->mockModel();
        // \Magento\Email\Model\Template is an abstract class
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Email\Model\Template');
        $model->setDesignConfig($config);
    }

    public function setDesignConfigExceptionDataProvider()
    {
        $this->mockModel();
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
        $this->mockModel();
        $testId = 9999;
        $this->model->setId($testId);
        $this->assertEquals($testId, $this->model->getId());
    }

    public function testIsValidForSend()
    {
        $this->mockModel();
        $this->assertTrue($this->model->isValidForSend());
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Email template 'foo' is not defined.
     */
    public function testGetTypeNonExistentType()
    {
        $this->mockModel();
        $this->model->setId('foo');
        $this->model->getType();
    }

    public function testGetTypeHtml()
    {
        $this->mockModel();
        $this->model->setId('customer_create_account_email_template');
        $this->assertEquals(\Magento\Framework\App\TemplateTypesInterface::TYPE_HTML, $this->model->getType());
    }

    public function testGetType()
    {
        $this->mockModel();
        $templateTypeId = 'test_template';
        $this->model->setTemplateType($templateTypeId);
        $this->assertEquals($templateTypeId, $this->model->getType());
    }

    public function testGetSendingException()
    {
        $this->mockModel();
        $this->assertNull($this->model->getSendingException());
    }

    public function testGetVariablesOptionArray()
    {
        $this->mockModel();
        $testTemplateVariables = '{"var data.name":"Sender Name","var data.email":"Sender Email"}';
        $this->model->setOrigTemplateVariables($testTemplateVariables);
        $variablesOptionArray = $this->model->getVariablesOptionArray();
        $this->assertEquals('{{var data.name}}', $variablesOptionArray[0]['value']);
        $this->assertEquals('Sender Name', $variablesOptionArray[0]['label']->getArguments()[0]);
        $this->assertEquals('{{var data.email}}', $variablesOptionArray[1]['value']);
        $this->assertEquals('Sender Email', $variablesOptionArray[1]['label']->getArguments()[0]);
    }

    public function testGetVariablesOptionArrayInGroup()
    {
        $this->mockModel();
        $testTemplateVariables = '{"var data.name":"Sender Name","var data.email":"Sender Email"}';
        $this->model->setOrigTemplateVariables($testTemplateVariables);
        $variablesOptionArray = $this->model->getVariablesOptionArray(true);
        $this->assertEquals('Template Variables', $variablesOptionArray['label']->getText());
        $this->assertEquals($this->model->getVariablesOptionArray(), $variablesOptionArray['value']);
    }

    /**
     * @expectedException \Magento\Framework\Exception\MailException
     * @expectedExceptionMessage The template Name must not be empty.
     */
    public function testBeforeSaveEmptyTemplateCode()
    {
        $this->mockModel();
        $this->model->beforeSave();
    }

    public function testBeforeSave()
    {
        $this->mockModel();
        $this->model->setTemplateCode('test template code');
        $this->model->beforeSave();
    }

    public function testProcessTemplate()
    {
        $this->mockModel();
        $this->model->setId('customer_create_account_email_template');
        $this->assertContains('<body style', $this->model->processTemplate());
    }

    public function testGetSubject()
    {
        $this->mockModel();
        $this->model->setVars(['foo', 'bar', 'baz']);
        $this->assertEquals('Subject', $this->model->getSubject());
    }

    public function testSetOptions()
    {
        $this->mockModel();
        $options = ['area' => 'test area', 'store' => 1];
        $this->model->setOptions($options);
        $this->assertEquals($options, $this->model->getDesignConfig()->getData());
    }
}
