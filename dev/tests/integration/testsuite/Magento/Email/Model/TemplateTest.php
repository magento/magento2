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
    protected $_model;

    /**
     * @var \Zend_Mail|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_mail;

    protected function setUp()
    {
        $this->_mail = $this->getMock(
            'Zend_Mail',
            ['send', 'addTo', 'addBcc', 'setReturnPath', 'setReplyTo'],
            ['utf-8']
        );
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
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
                $objectManager->create('Magento\Framework\Filesystem'),
                $objectManager->create('Magento\Framework\View\Asset\Repository'),
                $objectManager->create('Magento\Framework\View\FileSystem'),
                $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface'),
                $objectManager->get('Magento\Framework\ObjectManagerInterface'),
                $objectManager->get('Magento\Email\Model\Template\FilterFactory'),
                $objectManager->get('Magento\Email\Model\Template\Config'),
                $objectManager->get('Magento\Email\Model\TemplateFactory')
            ]
        )->getMock();
        $objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
        $this->_model->expects($this->any())->method('_getMail')->will($this->returnCallback([$this, 'getMail']));
        $this->_model->setSenderName('sender')->setSenderEmail('sender@example.com')->setTemplateSubject('Subject');
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
                )->getId()
            ]
        );
        $this->assertStringEndsWith($expectedViewUrl, $this->_model->getProcessedTemplate());
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
        $this->setUpThemeFallback($area);

        $this->_model->setTemplateText($templateText);

        $this->assertContains($expectedOutput, $this->_model->getProcessedTemplate());
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
        $this->setUpThemeFallback($area);

        $this->_model->setTemplateText($templateText);

        $this->assertContains($expectedOutput, $this->_model->getProcessedTemplate());
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
        $this->setUpThemeFallback($area);

        $this->_model->setTemplateText($templateText);

        // Allows for testing of templates overridden in backend
        if ($storeConfigPath) {
            $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
            $template = $objectManager->create('Magento\Email\Model\Template');
            $templateData = [
                'template_code' => 'some_unique_code',
                'template_type' => \Magento\Email\Model\Template::TYPE_HTML,
                'template_text' => $expectedOutput,
            ];
            $template->setData($templateData);
            $template->save();
            $templateId = $template->getId();

            // Store the ID of the newly created template in the system config so that this template will be loaded
            $objectManager->get(
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
                '{{template config_path="design/email/header_template"}}',
                '<html',
            ],
            'Template from module folder - frontend' => [
                \Magento\Framework\App\Area::AREA_FRONTEND,
                '{{template config_path="design/email/header_template"}}',
                '<html',
            ],
            'Template overridden in backend - adminhtml' => [
                \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                '{{template config_path="design/email/header_template"}}',
                'Header configured in backend - email loaded via adminhtml',
                'design/email/header_template',
            ],
            'Template overridden in backend - frontend' => [
                \Magento\Framework\App\Area::AREA_FRONTEND,
                '{{template config_path="design/email/header_template"}}',
                'Header configured in backend - email loaded via frontend',
                'design/email/header_template',
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
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $themes = ['frontend' => 'Vendor/custom_theme'];
        $design = $objectManager->create('Magento\Theme\Model\View\Design', ['themes' => $themes]);
        $objectManager->addSharedInstance($design, 'Magento\Theme\Model\View\Design');

        // It is important to test from both areas, as emails will get sent from both, so we need to ensure that the
        // inline CSS files get loaded properly from both areas.
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea($area);

        $collection = $objectManager->create('Magento\Theme\Model\Resource\Theme\Collection');

        // Hard-coding theme as we want to test the fallback structure to ensure that the parent/grandparent themes of
        // Vendor/custom_theme will be checked for CSS files
        $themeId = $collection->getThemeByFullPath('frontend/Vendor/custom_theme')->getId();

        $objectManager->get(
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
                'store' => $objectManager->get(
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
                )->getId()
            ]
        );
        $this->assertStringEndsWith($expectedViewUrl, $this->_model->getProcessedTemplateSubject([]));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetDefaultEmailLogo()
    {
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
        // \Magento\Email\Model\Template is an abstract class
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Email\Model\Template');
        $model->setDesignConfig($config);
    }

    public function setDesignConfigExceptionDataProvider()
    {
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
        $testId = 9999;
        $this->_model->setId($testId);
        $this->assertEquals($testId, $this->_model->getId());
    }

    public function testIsValidForSend()
    {
        $this->assertTrue($this->_model->isValidForSend());
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Email template 'foo' is not defined.
     */
    public function testGetTypeNonExistentType()
    {
        $this->_model->setId('foo');
        $this->_model->getType();
    }

    public function testGetTypeHtml()
    {
        $this->_model->setId('customer_create_account_email_template');
        $this->assertEquals(\Magento\Framework\App\TemplateTypesInterface::TYPE_HTML, $this->_model->getType());
    }

    public function testGetType()
    {
        $templateTypeId = 'test_template';
        $this->_model->setTemplateType($templateTypeId);
        $this->assertEquals($templateTypeId, $this->_model->getType());
    }

    public function testGetPreparedTemplateText()
    {
        $this->_model->loadDefault('customer_create_account_email_template');
        $this->assertContains(
            '<body style',
            $this->_model->getPreparedTemplateText(
                $this->_model->getTemplateText()
            )
        );
    }

    public function testGetSendingException()
    {
        $this->assertNull($this->_model->getSendingException());
    }

    public function testGetVariablesOptionArray()
    {
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
        $testTemplateVariables = '{"var data.name":"Sender Name","var data.email":"Sender Email"}';
        $this->_model->setOrigTemplateVariables($testTemplateVariables);
        $variablesOptionArray = $this->_model->getVariablesOptionArray(true);
        $this->assertEquals('Template Variables', $variablesOptionArray['label']->getText());
        $this->assertEquals($this->_model->getVariablesOptionArray(), $variablesOptionArray['value']);
    }

    /**
     * @expectedException \Magento\Framework\Exception\MailException
     * @expectedExceptionMessage The template Name must not be empty.
     */
    public function testBeforeSaveEmptyTemplateCode()
    {
        $this->_model->beforeSave();
    }

    public function testBeforeSave()
    {
        $this->_model->setTemplateCode('test template code');
        $this->_model->beforeSave();
    }

    public function testProcessTemplate()
    {
        $this->_model->setId('customer_create_account_email_template');
        $this->assertContains('<body style', $this->_model->processTemplate());
    }

    public function testGetSubject()
    {
        $this->_model->setVars(['foo', 'bar', 'baz']);
        $this->assertEquals('Subject', $this->_model->getSubject());
    }

    public function testSetOptions()
    {
        $options = ['area' => 'test area', 'store' => 1];
        $this->_model->setOptions($options);
        $this->assertEquals($options, $this->_model->getDesignConfig()->getData());
    }
}
