<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Template;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Phrase;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Email\Model\Template\Filter
     */
    protected $_model = null;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->_model = $this->_objectManager->create(
            'Magento\Email\Model\Template\Filter'
        );
    }

    /**
     * Isolation level has been raised in order to flush themes configuration in-memory cache
     *
     * @magentoAppArea frontend
     */
    public function testViewDirective()
    {
        $url = $this->_model->viewDirective(
            ['{{view url="Magento_Theme::favicon.ico"}}', 'view', ' url="Magento_Theme::favicon.ico"']
        );
        $this->assertStringEndsWith('favicon.ico', $url);
    }

    /**
     * Isolation level has been raised in order to flush themes configuration in-memory cache
     *
     * @magentoAppArea frontend
     */
    public function testBlockDirective()
    {
        $class = 'Magento\\\\Theme\\\\Block\\\\Html\\\\Footer';
        $data = ["{{block class='$class' name='test.block' template='Magento_Theme::html/footer.phtml'}}",
                'block',
                " class='$class' name='test.block' template='Magento_Theme::html/footer.phtml'",

            ];
        $html = $this->_model->blockDirective($data);
        $this->assertContains('<div class="footer-container">', $html);
    }

    /**
     * @magentoConfigFixture current_store web/unsecure/base_link_url http://example.com/
     * @magentoConfigFixture admin_store web/unsecure/base_link_url http://example.com/
     */
    public function testStoreDirective()
    {
        $url = $this->_model->storeDirective(
            ['{{store direct_url="arbitrary_url/"}}', 'store', ' direct_url="arbitrary_url/"']
        );
        $this->assertStringMatchesFormat('http://example.com/%sarbitrary_url/', $url);

        $url = $this->_model->storeDirective(
            ['{{store url="translation/ajax/index"}}', 'store', ' url="translation/ajax/index"']
        );
        $this->assertStringMatchesFormat('http://example.com/%stranslation/ajax/index/', $url);

        $this->_model->setStoreId(0);
        $backendUrlModel = $this->_objectManager->create('Magento\Backend\Model\Url');
        $this->_model->setUrlModel($backendUrlModel);
        $url = $this->_model->storeDirective(
            ['{{store url="translation/ajax/index"}}', 'store', ' url="translation/ajax/index"']
        );
        $this->assertStringMatchesFormat('http://example.com/index.php/backend/translation/ajax/index/%A', $url);
    }

    /**
     * @magentoDataFixture Magento/Email/Model/_files/design/themes.php
     * @magentoAppIsolation enabled
     * @dataProvider layoutDirectiveDataProvider
     *
     * @param string $area
     * @param string $directiveParams
     * @param string $expectedOutput
     */
    public function testLayoutDirective($area, $directiveParams, $expectedOutput)
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize(
            [
                Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS => [
                    DirectoryList::THEMES => [
                        'path' => dirname(__DIR__) . '/_files/design',
                    ],
                ],
            ]
        );
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Email\Model\Template\Filter'
        );

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $themes = ['frontend' => 'Magento/default', 'adminhtml' => 'Magento/default'];
        $design = $objectManager->create('Magento\Theme\Model\View\Design', ['themes' => $themes]);
        $objectManager->addSharedInstance($design, 'Magento\Theme\Model\View\Design');

        \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea($area);

        $collection = $objectManager->create('Magento\Theme\Model\Resource\Theme\Collection');
        $themeId = $collection->getThemeByFullPath('frontend/Magento/default')->getId();
        $objectManager->get(
            'Magento\Framework\App\Config\MutableScopeConfigInterface'
        )->setValue(
            \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
            $themeId,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        /** @var $layout \Magento\Framework\View\LayoutInterface */
        $layout = $objectManager->create('Magento\Framework\View\Layout');
        $objectManager->addSharedInstance($layout, 'Magento\Framework\View\Layout');
        $objectManager->get('Magento\Framework\View\DesignInterface')->setDesignTheme('Magento/default');

        $actualOutput = $this->_model->layoutDirective(
            ['{{layout ' . $directiveParams . '}}', 'layout', ' ' . $directiveParams]
        );
        $this->assertEquals($expectedOutput, trim($actualOutput));
    }

    /**
     * @return array
     */
    public function layoutDirectiveDataProvider()
    {
        $result = [
            'area parameter - omitted' => [
                'adminhtml',
                'handle="email_template_test_handle"',
                'Email content for frontend/Magento/default theme',
            ],
            'area parameter - frontend' => [
                'adminhtml',
                'handle="email_template_test_handle" area="frontend"',
                'Email content for frontend/Magento/default theme',
            ],
            'area parameter - backend' => [
                'frontend',
                'handle="email_template_test_handle" area="adminhtml"',
                'Email content for adminhtml/Magento/default theme',
            ],
            'custom parameter' => [
                'frontend',
                'handle="email_template_test_handle" template="Magento_Email::sample_email_content_custom.phtml"',
                'Custom Email content for frontend/Magento/default theme',
            ],
        ];
        return $result;
    }

    /**
     * @param $directive
     * @param $translations
     * @param $expectedResult
     * @internal param $translatorData
     * @dataProvider transDirectiveDataProvider
     */
    public function testTransDirective($directive, $translations, $expectedResult)
    {
        $renderer = Phrase::getRenderer();

        $translator = $this->getMockBuilder('\Magento\Framework\Translate')
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();

        $translator->expects($this->atLeastOnce())
            ->method('getData')
            ->will($this->returnValue($translations));

        $this->_objectManager->addSharedInstance($translator, 'Magento\Framework\Translate');
        $this->_objectManager->removeSharedInstance('Magento\Framework\Phrase\Renderer\Translate');
        Phrase::setRenderer($this->_objectManager->create('Magento\Framework\Phrase\RendererInterface'));

        $this->assertEquals($expectedResult, $this->_model->filter($directive));

        Phrase::setRenderer($renderer);
    }

    /**
     * @return array
     */
    public function transDirectiveDataProvider()
    {
        return [
            [
                '{{trans "foobar"}}',
                [],
                'foobar',
            ],
            [
                '{{trans "foobar"}}',
                ['foobar' => 'barfoo'],
                'barfoo',
            ]
        ];
    }

    /**
     * Ensures that the css directive will successfully compile and output contents of a LESS file,
     * as well as supporting loading files from a theme fallback structure.
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Email/Model/_files/design/themes.php
     * @magentoAppIsolation enabled
     * @dataProvider cssDirectiveDataProvider
     *
     * @param $directiveParams
     * @param $expectedOutput
     */
    public function testCssDirective($directiveParams, $expectedOutput)
    {
        $this->setUpDesignParams();
        $this->_model->setStoreId('fixturestore');

        $this->assertContains($expectedOutput, $this->_model->cssDirective(
            ['{{css ' . $directiveParams . '}}', 'css', ' ' . $directiveParams]
        ));
    }

    /**
     * @return array
     */
    public function cssDirectiveDataProvider()
    {
        return [
            'CSS from theme' => [
                'file="css/email-1.css"',
                'color: #111;'
            ],
            'CSS from parent theme' => [
                'file="css/email-2.css"',
                'color: #222;'
            ],
            'CSS from grandparent theme' => [
                'file="css/email-3.css"',
                'color: #333;'
            ],
            'Missing file parameter' => [
                '',
                '/* "file" parameter must be specified */'
            ],
            'Empty or missing file' => [
                'file="css/non-existent-file.css"',
                '/* Contents of css/non-existent-file.css could not be loaded or is empty */'
            ],
            'File with compilation error results in error message' => [
                'file="css/file-with-error.css"',
                \Magento\Framework\Css\PreProcessor\Adapter\Oyejorge::ERROR_MESSAGE_PREFIX,
            ],
        ];
    }

    /**
     * Ensures that the inlinecss directive will successfully load and inline CSS to HTML markup,
     * as well as supporting loading files from a theme fallback structure.
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Email/Model/_files/design/themes.php
     * @magentoAppIsolation enabled
     * @dataProvider inlinecssDirectiveDataProvider
     *
     * @param string $templateText
     * @param string $expectedOutput
     * @param bool $productionMode
     * @param bool $plainTemplateMode
     * @param bool $isChildTemplateMode
     */
    public function testInlinecssDirective(
        $templateText,
        $expectedOutput,
        $productionMode = false,
        $plainTemplateMode = false,
        $isChildTemplateMode = false
    ) {
        $this->setUpDesignParams();

        $this->_model->setPlainTemplateMode($plainTemplateMode);
        $this->_model->setIsChildTemplate($isChildTemplateMode);

        if ($productionMode) {
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
                ->setMode(\Magento\Framework\App\State::MODE_PRODUCTION);
        }

        $this->assertContains($expectedOutput, $this->_model->filter($templateText));
    }

    /**
     * @return array
     */
    public function inlinecssDirectiveDataProvider()
    {
        return [
            'CSS from theme' => [
                '<html><p></p> {{inlinecss file="css/email-inline-1.css"}}</html>',
                '<p style="color: #111; text-align: left;">',
            ],
            'CSS from parent theme' => [
                '<html><p></p> {{inlinecss file="css/email-inline-2.css"}}</html>',
                '<p style="color: #222; text-align: left;">',
            ],
            'CSS from grandparent theme' => [
                '<html><p></p> {{inlinecss file="css/email-inline-3.css"}}',
                '<p style="color: #333; text-align: left;">',
            ],
            'Non-existent file results in unmodified markup' => [
                '<html><p></p> {{inlinecss file="css/non-existent-file.css"}}</html>',
                '<html><p></p> </html>',
            ],
            'Plain template mode results in unmodified markup' => [
                '<html><p></p> {{inlinecss file="css/email-inline-1.css"}}</html>',
                '<html><p></p> </html>',
                false,
                true,
            ],
            'Child template mode results in unmodified directive' => [
                '<html><p></p> {{inlinecss file="css/email-inline-1.css"}}</html>',
                '<html><p></p> {{inlinecss file="css/email-inline-1.css"}}</html>',
                false,
                false,
                true,
            ],
            'Production mode - File with compilation error results in unmodified markup' => [
                '<html><p></p> {{inlinecss file="css/file-with-error.css"}}</html>',
                '<html><p></p> </html>',
                true,
            ],
            'Developer mode - File with compilation error results in error message' => [
                '<html><p></p> {{inlinecss file="css/file-with-error.css"}}</html>',
                \Magento\Framework\Css\PreProcessor\Adapter\Oyejorge::ERROR_MESSAGE_PREFIX,
                false,
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Email/Model/_files/design/themes.php
     * @magentoAppIsolation enabled
     * @dataProvider inlinecssDirectiveThrowsExceptionWhenMissingParameterDataProvider
     *
     * @param string $templateText
     */
    public function testInlinecssDirectiveThrowsExceptionWhenMissingParameter($templateText)
    {
        $this->setUpDesignParams();

        $this->_model->filter($templateText);
    }

    /**
     * @return array
     */
    public function inlinecssDirectiveThrowsExceptionWhenMissingParameterDataProvider()
    {
        return [
            'Missing "file" parameter' => [
                '{{inlinecss}}',
            ],
            'Missing "file" parameter value' => [
                '{{inlinecss file=""}}',
            ],
        ];
    }

    /**
     * Setup the design params
     */
    protected function setUpDesignParams()
    {
        $themeCode = 'Vendor/custom_theme';
        $this->_model->setDesignParams(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'theme' => $themeCode,
                'locale' => \Magento\Setup\Module\I18n\Locale::DEFAULT_SYSTEM_LOCALE,
            ]
        );
    }
}
