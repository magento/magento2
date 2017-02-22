<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model;

use Magento\Backend\App\Area\FrontNameResolver as BackendFrontNameResolver;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Template|\PHPUnit_Framework_MockObject_MockObject
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
        $this->objectManager = Bootstrap::getObjectManager();
    }

    protected function mockModel($filesystem = null)
    {
        if (!$filesystem) {
            $filesystem = $this->objectManager->create('Magento\Framework\Filesystem');
        }

        $this->mail = $this->getMock(
            'Zend_Mail',
            ['send', 'addTo', 'addBcc', 'setReturnPath', 'setReplyTo'],
            ['utf-8']
        );

        $this->model = $this->getMockBuilder('Magento\Email\Model\Template')
            ->setMethods(['_getMail'])
            ->setConstructorArgs([
                $this->objectManager->get('Magento\Framework\Model\Context'),
                $this->objectManager->get('Magento\Framework\View\DesignInterface'),
                $this->objectManager->get('Magento\Framework\Registry'),
                $this->objectManager->get('Magento\Store\Model\App\Emulation'),
                $this->objectManager->get('Magento\Store\Model\StoreManager'),
                $this->objectManager->create('Magento\Framework\View\Asset\Repository'),
                $filesystem,
                $this->objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface'),
                $this->objectManager->get('Magento\Email\Model\Template\Config'),
                $this->objectManager->get('Magento\Email\Model\TemplateFactory'),
                $this->objectManager->get('Magento\Framework\Filter\FilterManager'),
                $this->objectManager->get('Magento\Framework\UrlInterface'),
                $this->objectManager->get('Magento\Email\Model\Template\FilterFactory'),
            ])
            ->getMock();

        $this->objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');

        $this->model->expects($this->any())->method('_getMail')->will($this->returnCallback([$this, 'getMail']));
        $this->model->setSenderName('sender')->setSenderEmail('sender@example.com')->setTemplateSubject('Subject');
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
            $this->objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId(),
            $filter->getStoreId()
        );

        $filter = $this->objectManager->create('Magento\Email\Model\Template\Filter');
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
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testGetProcessedTemplate()
    {
        $this->mockModel();
        $this->objectManager->get('Magento\Framework\App\AreaList')
            ->getArea(Area::AREA_FRONTEND)
            ->load();

        $expectedViewUrl = '/frontend/Magento/blank/en_US/Magento_Theme/favicon.ico';
        $this->model->setDesignConfig([
            'area' => 'frontend',
            'store' => $this->objectManager->get('Magento\Store\Model\StoreManagerInterface')
                ->getStore('fixturestore')
                ->getId(),
        ]);
        $this->model->setTemplateText('{{view url="Magento_Theme::favicon.ico"}}');

        $this->setNotDefaultThemeForFixtureStore();
        $this->assertStringEndsNotWith($expectedViewUrl, $this->model->getProcessedTemplate());

        $this->setDefaultThemeForFixtureStore();
        $this->assertStringEndsWith($expectedViewUrl, $this->model->getProcessedTemplate());
    }

    /**
     * Test template directive to ensure that templates can be loaded from modules
     *
     * @param string $area
     * @param string $templateId
     * @param string $expectedOutput
     * @param bool $mockThemeFallback
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoComponentsDir Magento/Email/Model/_files/design
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @dataProvider templateFallbackDataProvider
     */
    public function testTemplateFallback($area, $templateId, $expectedOutput, $mockThemeFallback = false)
    {
        $this->mockModel();

        if ($mockThemeFallback == BackendFrontNameResolver::AREA_CODE) {
            $this->setUpAdminThemeFallback();
        } elseif ($mockThemeFallback == Area::AREA_FRONTEND) {
            $this->setUpThemeFallback($area);
        }

        $this->model->setId($templateId);

        $this->assertContains($expectedOutput, $this->model->processTemplate());
    }

    /**
     * @return array
     */
    public function templateFallbackDataProvider()
    {
        return [
            'Template from module - admin' => [
                BackendFrontNameResolver::AREA_CODE,
                'customer_create_account_email_template',
                'To sign in to our site, use these credentials during checkout',
            ],
            'Template from module - frontend' => [
                Area::AREA_FRONTEND,
                'customer_create_account_email_template',
                'To sign in to our site, use these credentials during checkout',
            ],
            'Template from theme - frontend' => [
                Area::AREA_FRONTEND,
                'customer_create_account_email_template',
                'customer_create_account_email_template template from Vendor/custom_theme',
                Area::AREA_FRONTEND,
            ],
            'Template from parent theme - frontend' => [
                Area::AREA_FRONTEND,
                'customer_create_account_email_confirmation_template',
                'customer_create_account_email_confirmation_template template from Vendor/default',
                Area::AREA_FRONTEND,
            ],
            'Template from grandparent theme - frontend' => [
                Area::AREA_FRONTEND,
                'customer_create_account_email_confirmed_template',
                'customer_create_account_email_confirmed_template template from Magento/default',
                Area::AREA_FRONTEND,
            ],
            'Template from grandparent theme - adminhtml' => [
                BackendFrontNameResolver::AREA_CODE,
                'catalog_productalert_cron_error_email_template',
                'catalog_productalert_cron_error_email_template template from Magento/default',
                BackendFrontNameResolver::AREA_CODE,
            ],

        ];
    }

    /**
     * Test template directive to ensure that templates can be loaded from modules, overridden in backend, and
     * overridden in themes
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoComponentsDir Magento/Email/Model/_files/design
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @dataProvider templateDirectiveDataProvider
     *
     * @param string $area
     * @param int $templateType
     * @param string $templateText
     * @param string $assertContains
     * @param string $assertNotContains
     * @param string $storeConfigPath
     * @param bool $mockAdminTheme
     */
    public function testTemplateDirective(
        $area,
        $templateType,
        $templateText,
        $assertContains,
        $assertNotContains = null,
        $storeConfigPath = null,
        $mockAdminTheme = false
    ) {
        $this->mockModel();

        if ($mockAdminTheme) {
            $this->setUpAdminThemeFallback();
        } else {
            $this->setUpThemeFallback($area);
        }

        $this->model->setTemplateType($templateType);
        $this->model->setTemplateText($templateText);

        // Allows for testing of templates overridden in backend
        if ($storeConfigPath) {
            $template = $this->objectManager->create('Magento\Email\Model\Template');
            $templateData = [
                'template_code' => 'some_unique_code',
                'template_type' => $templateType,
                'template_text' => $assertContains,
            ];
            $template->setData($templateData);
            $template->save();

            // Store the ID of the newly created template in the system config so that this template will be loaded
            $this->objectManager->get('Magento\Framework\App\Config\MutableScopeConfigInterface')
                ->setValue($storeConfigPath, $template->getId(), ScopeInterface::SCOPE_STORE, 'fixturestore');
        }

        $this->assertContains($assertContains, $this->model->getProcessedTemplate());
        if ($assertNotContains) {
            $this->assertNotContains($assertNotContains, $this->model->getProcessedTemplate());
        }
    }

    /**
     * @return array
     */
    public function templateDirectiveDataProvider()
    {
        return [
            'Template from module folder - adminhtml' => [
                BackendFrontNameResolver::AREA_CODE,
                TemplateTypesInterface::TYPE_HTML,
                '{{template config_path="design/email/footer_template"}}',
                "</table>\n<!-- End wrapper table -->",
            ],
            'Template from module folder - frontend' => [
                Area::AREA_FRONTEND,
                TemplateTypesInterface::TYPE_HTML,
                '{{template config_path="design/email/footer_template"}}',
                "</table>\n<!-- End wrapper table -->",
            ],
            'Template from module folder - plaintext' => [
                Area::AREA_FRONTEND,
                TemplateTypesInterface::TYPE_TEXT,
                '{{template config_path="design/email/footer_template"}}',
                'Thank you',
                "</table>\n<!-- End wrapper table -->",
            ],
            'Template overridden in backend - adminhtml' => [
                BackendFrontNameResolver::AREA_CODE,
                TemplateTypesInterface::TYPE_HTML,
                '{{template config_path="design/email/footer_template"}}',
                '<b>Footer configured in backend - email loaded via adminhtml</b>',
                null,
                'design/email/footer_template',
            ],
            'Template overridden in backend - frontend' => [
                Area::AREA_FRONTEND,
                TemplateTypesInterface::TYPE_HTML,
                '{{template config_path="design/email/footer_template"}}',
                '<b>Footer configured in backend - email loaded via frontend</b>',
                null,
                'design/email/footer_template',
            ],
            'Template from theme - frontend' => [
                Area::AREA_FRONTEND,
                TemplateTypesInterface::TYPE_HTML,
                '{{template config_path="customer/create_account/email_template"}}',
                '<b>customer_create_account_email_template template from Vendor/custom_theme</b>',
            ],
            'Template from parent theme - frontend' => [
                Area::AREA_FRONTEND,
                TemplateTypesInterface::TYPE_HTML,
                '{{template config_path="customer/create_account/email_confirmation_template"}}',
                '<b>customer_create_account_email_confirmation_template template from Vendor/default</b>',
            ],
            'Template from grandparent theme - frontend' => [
                Area::AREA_FRONTEND,
                TemplateTypesInterface::TYPE_HTML,
                '{{template config_path="customer/create_account/email_confirmed_template"}}',
                '<b>customer_create_account_email_confirmed_template template from Magento/default</b>',
            ],
            'Template from grandparent theme - adminhtml' => [
                BackendFrontNameResolver::AREA_CODE,
                TemplateTypesInterface::TYPE_HTML,
                '{{template config_path="catalog/productalert_cron/error_email_template"}}',
                '<b>catalog_productalert_cron_error_email_template template from Magento/default</b>',
                null,
                null,
                true,
            ],
        ];
    }

    /**
     * Ensure that the template_styles variable contains styles from either <!--@styles @--> or the "Template Styles"
     * textarea in backend, depending on whether template was loaded from filesystem or DB.
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoComponentsDir Magento/Email/Model/_files/design
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @dataProvider templateStylesVariableDataProvider
     *
     * @param string $area
     * @param string $expectedOutput
     * @param array $unexpectedOutputs
     * @param array $templateForDatabase
     */
    public function testTemplateStylesVariable($area, $expectedOutput, $unexpectedOutputs, $templateForDatabase = [])
    {
        if (count($templateForDatabase)) {
            $this->mockModel();
            $this->setUpThemeFallback($area);

            $template = $this->objectManager->create('Magento\Email\Model\Template');
            $template->setData($templateForDatabase);
            $template->save();
            $templateId = $template->getId();

            $this->model->load($templateId);
        } else {
            // <!--@styles @--> parsing only via the loadDefault method. Since email template files won't contain
            // @styles comments by default, it is necessary to mock an object to return testable contents
            $themeDirectory = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\ReadInterface')
                ->disableOriginalConstructor()
                ->setMethods([
                    'readFile',
                ])
                ->getMockForAbstractClass();

            $themeDirectory->expects($this->once())
                ->method('readFile')
                ->will($this->returnValue('<!--@styles p { color: #111; } @--> {{var template_styles}}'));

            $filesystem = $this->getMockBuilder('\Magento\Framework\Filesystem')
                ->disableOriginalConstructor()
                ->setMethods(['getDirectoryRead'])
                ->getMock();

            $filesystem->expects($this->once())
                ->method('getDirectoryRead')
                ->with(DirectoryList::ROOT)
                ->will($this->returnValue($themeDirectory));

            $this->mockModel($filesystem);

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
                BackendFrontNameResolver::AREA_CODE,
                'p { color: #111; }',
                [
                    '<!--@styles',
                    '{{var template_styles}}',
                ],
            ],
            'Styles from <!--@styles @--> comment - frontend' => [
                Area::AREA_FRONTEND,
                'p { color: #111; }',
                [
                    '<!--@styles',
                    '{{var template_styles}}',
                ],
            ],
            'Styles from "Template Styles" textarea from backend - adminhtml' => [
                BackendFrontNameResolver::AREA_CODE,
                'p { color: #222; }',
                ['{{var template_styles}}'],
                [
                    'template_code' => 'some_unique_code',
                    'template_type' => Template::TYPE_HTML,
                    'template_text' => 'Footer from database {{var template_styles}}',
                    'template_styles' => 'p { color: #222; }',
                ],
            ],
            'Styles from "Template Styles" textarea from backend - frontend' => [
                Area::AREA_FRONTEND,
                'p { color: #333; }',
                ['{{var template_styles}}'],
                [
                    'template_code' => 'some_unique_code',
                    'template_type' => Template::TYPE_HTML,
                    'template_text' => 'Footer from database {{var template_styles}}',
                    'template_styles' => 'p { color: #333; }',
                ],
            ],
        ];
    }

    /**
     * Setup the theme fallback structure and set the Vendor_EmailTest/custom_theme as the current theme for
     * 'fixturestore' store
     */
    protected function setUpAdminThemeFallback()
    {
        $themes = [BackendFrontNameResolver::AREA_CODE => 'Vendor_EmailTest/custom_theme'];
        $design = $this->objectManager->create('Magento\Theme\Model\View\Design', ['themes' => $themes]);
        $this->objectManager->addSharedInstance($design, 'Magento\Theme\Model\View\Design');
        /** @var \Magento\Theme\Model\Theme\Registration $registration */
        $registration = $this->objectManager->get(
            'Magento\Theme\Model\Theme\Registration'
        );
        $registration->register();

        // The Vendor_EmailTest/custom_theme adminhtml theme is set in the
        // dev/tests/integration/testsuite/Magento/Email/Model/_files/design/themes.php file, as it must be set
        // before the adminhtml area is loaded below.

        Bootstrap::getInstance()->loadArea(BackendFrontNameResolver::AREA_CODE);

        /** @var \Magento\Store\Model\Store $adminStore */
        $adminStore = $this->objectManager->create('Magento\Store\Model\Store')
            ->load(Store::ADMIN_CODE);

        $this->model->setDesignConfig([
            'area' => 'adminhtml',
            'store' => $adminStore->getId(),
        ]);
    }

    /**
     * Setup the theme fallback structure and set the Vendor_EmailTest/custom_theme as the current theme
     * for 'fixturestore' store
     *
     * @param $area
     */
    protected function setUpThemeFallback($area)
    {
        /** @var \Magento\Theme\Model\Theme\Registration $registration */
        $registration = $this->objectManager->get(
            'Magento\Theme\Model\Theme\Registration'
        );
        $registration->register();

        // It is important to test from both areas, as emails will get sent from both, so we need to ensure that the
        // inline CSS files get loaded properly from both areas.
        Bootstrap::getInstance()->loadArea($area);

        $collection = $this->objectManager->create('Magento\Theme\Model\ResourceModel\Theme\Collection');

        // Hard-coding theme as we want to test the fallback structure to ensure that the parent/grandparent themes of
        // Vendor_EmailTest/custom_theme will be checked for CSS files
        $themeId = $collection->getThemeByFullPath('frontend/Vendor_EmailTest/custom_theme')->getId();

        $this->objectManager->get('Magento\Framework\App\Config\MutableScopeConfigInterface')
            ->setValue(
                DesignInterface::XML_PATH_THEME_ID,
                $themeId,
                ScopeInterface::SCOPE_STORE,
                'fixturestore'
            );

        $this->model->setDesignConfig([
            'area' => 'frontend',
            'store' => $this->objectManager->get('Magento\Store\Model\StoreManagerInterface')
                ->getStore('fixturestore')
                ->getId(),
        ]);
    }

    /**
     * Set 'Magento/luma' for the 'fixturestore' store.
     * Application isolation is required, if a test uses this method.
     */
    protected function setNotDefaultThemeForFixtureStore()
    {
        /** @var \Magento\Framework\View\Design\ThemeInterface $theme */
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
     * Set 'Magento/blank' for the 'fixturestore' store.
     * Application isolation is required, if a test uses this method.
     */
    protected function setDefaultThemeForFixtureStore()
    {
        /** @var \Magento\Framework\View\Design\ThemeInterface $theme */
        $theme = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Design\ThemeInterface'
        );
        $theme->load('Magento/blank', 'theme_path');
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
        $this->objectManager->get('Magento\Framework\App\AreaList')
            ->getArea(Area::AREA_FRONTEND)
            ->load();

        $this->model->setTemplateSubject('{{view url="Magento_Theme::favicon.ico"}}');
        $this->model->setDesignConfig([
            'area' => 'frontend',
            'store' => $this->objectManager->get('Magento\Store\Model\StoreManagerInterface')
                ->getStore('fixturestore')
                ->getId(),
        ]);

        $this->setNotDefaultThemeForFixtureStore();
        $this->assertStringMatchesFormat(
            '%s/frontend/Magento/luma/en_US/Magento_Theme/favicon.ico',
            $this->model->getProcessedTemplateSubject([])
        );

        $this->setDefaultThemeForFixtureStore();
        $this->assertStringMatchesFormat(
            '%s/frontend/Magento/blank/en_US/Magento_Theme/favicon.ico',
            $this->model->getProcessedTemplateSubject([])
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetDefaultEmailLogo()
    {
        $this->mockModel();
        $this->objectManager->get('Magento\Framework\App\AreaList')
            ->getArea(Area::AREA_FRONTEND)
            ->load();

        $this->assertStringEndsWith(
            '/frontend/Magento/luma/en_US/Magento_Email/logo_email.png',
            $this->model->getDefaultEmailLogo()
        );
    }

    /**
     * @param $config
     * @dataProvider setDesignConfigExceptionDataProvider
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testSetDesignConfigException($config)
    {
        $this->mockModel();
        $model = $this->objectManager->create('Magento\Email\Model\Template');
        $model->setDesignConfig($config);
    }

    public function setDesignConfigExceptionDataProvider()
    {
        $storeId = Bootstrap::getObjectManager()->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore()
            ->getId();

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
        $this->assertEquals(TemplateTypesInterface::TYPE_HTML, $this->model->getType());
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
     * @expectedExceptionMessage Please enter a template name.
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
        $this->assertContains('<body', $this->model->processTemplate());
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
