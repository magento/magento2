<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Template;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Phrase;
use Magento\Framework\View\Asset\ContentProcessorInterface;
use Magento\Setup\Module\I18n\Locale;
use Magento\Theme\Block\Html\Footer;

/**
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FilterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Email\Model\Template\Filter
     */
    protected $model;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->model = $this->objectManager->create(
            \Magento\Email\Model\Template\Filter::class
        );
    }

    /**
     * Isolation level has been raised in order to flush themes configuration in-memory cache
     *
     * @magentoAppArea frontend
     */
    public function testViewDirective()
    {
        $url = $this->model->viewDirective(
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
        $class = Footer::class;
        $data = [
            "{{block class='$class' name='test.block' template='Magento_Theme::html/footer.phtml'}}",
            'block',
            " class='$class' name='test.block' template='Magento_Theme::html/footer.phtml'",
        ];
        $html = $this->model->blockDirective($data);
        $this->assertContains('<div class="footer-container">', $html);
    }

    /**
     * @magentoConfigFixture current_store web/unsecure/base_link_url http://example.com/
     * @magentoConfigFixture admin_store web/unsecure/base_link_url http://example.com/
     */
    public function testStoreDirective()
    {
        $url = $this->model->storeDirective(
            ['{{store direct_url="arbitrary_url/"}}', 'store', ' direct_url="arbitrary_url/"']
        );
        $this->assertStringMatchesFormat('http://example.com/%sarbitrary_url/', $url);

        $url = $this->model->storeDirective(
            ['{{store url="translation/ajax/index"}}', 'store', ' url="translation/ajax/index"']
        );
        $this->assertStringMatchesFormat('http://example.com/%stranslation/ajax/index/', $url);

        $this->model->setStoreId(0);
        $backendUrlModel = $this->objectManager->create(\Magento\Backend\Model\Url::class);
        $this->model->setUrlModel($backendUrlModel);
        $url = $this->model->storeDirective(
            ['{{store url="translation/ajax/index"}}', 'store', ' url="translation/ajax/index"']
        );
        $this->assertStringMatchesFormat('http://example.com/index.php/backend/translation/ajax/index/%A', $url);
    }

    /**
     * @magentoComponentsDir Magento/Email/Model/_files/design
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @dataProvider layoutDirectiveDataProvider
     *
     * @param string $area
     * @param string $directiveParams
     * @param string $expectedOutput
     */
    public function testLayoutDirective($area, $directiveParams, $expectedOutput)
    {
        /** @var \Magento\Theme\Model\Theme\Registration $registration */
        $registration = $this->objectManager->get(\Magento\Theme\Model\Theme\Registration::class);
        $registration->register();
        $this->model = $this->objectManager->create(\Magento\Email\Model\Template\Filter::class);
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea($area);
        /** @var $layout \Magento\Framework\View\LayoutInterface */
        $layout = $this->objectManager->create(\Magento\Framework\View\Layout::class);
        $this->objectManager->addSharedInstance($layout, \Magento\Framework\View\Layout::class);
        $this->objectManager->get(\Magento\Framework\View\DesignInterface::class)
            ->setDesignTheme('Magento_EmailTest/default');

        $actualOutput = $this->model->layoutDirective(
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
                '<strong>Email content for frontend/Magento/default theme</strong>',
            ],
            'area parameter - frontend' => [
                'adminhtml',
                'handle="email_template_test_handle" area="frontend"',
                '<strong>Email content for frontend/Magento/default theme</strong>',
            ],
            'area parameter - backend' => [
                'frontend',
                'handle="email_template_test_handle" area="adminhtml"',
                '<strong>Email content for adminhtml/Magento/default theme</strong>',
            ],
            'custom parameter' => [
                'frontend',
                'handle="email_template_test_handle" template="Magento_Email::sample_email_content_custom.phtml"',
                '<strong>Custom Email content for frontend/Magento/default theme</strong>',
            ],
        ];
        return $result;
    }

    /**
     * @param $directive
     * @param $translations
     * @param $expectedResult
     * @param array $variables
     * @internal param $translatorData
     * @dataProvider transDirectiveDataProvider
     */
    public function testTransDirective($directive, $translations, $expectedResult, $variables = [])
    {
        $renderer = Phrase::getRenderer();

        $translator = $this->getMockBuilder(\Magento\Framework\Translate::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();

        $translator->method('getData')
            ->willReturn($translations);

        if (!empty($variables)) {
            $this->model->setVariables($variables);
        }

        $this->objectManager->addSharedInstance($translator, \Magento\Framework\Translate::class);
        $this->objectManager->removeSharedInstance(\Magento\Framework\Phrase\Renderer\Translate::class);
        Phrase::setRenderer($this->objectManager->create(\Magento\Framework\Phrase\RendererInterface::class));

        $this->assertEquals($expectedResult, $this->model->filter($directive));

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
            ],
            'empty directive' => [
                '{{trans}}',
                [],
                '',
            ],
            'empty string' => [
                '{{trans ""}}',
                [],
                '',
            ],
            'no padding' => [
                '{{trans"Hello cruel coder..."}}',
                [],
                'Hello cruel coder...',
            ],
            'multi-line padding' => [
                "{{trans \t\n\r'Hello cruel coder...' \t\n\r}}",
                [],
                'Hello cruel coder...',
            ],
            'capture escaped double-quotes inside text' => [
                '{{trans "Hello \"tested\" world!"}}',
                [],
                'Hello &quot;tested&quot; world!',
            ],
            'capture escaped single-quotes inside text' => [
                "{{trans 'Hello \\'tested\\' world!'|escape}}",
                [],
                "Hello &#039;tested&#039; world!",
            ],
            'filter with params' => [
                "{{trans 'Hello \\'tested\\' world!'|escape:html}}",
                [],
                "Hello &#039;tested&#039; world!",
            ],
            'basic var' => [
                '{{trans "Hello %adjective world!" adjective="tested"}}',
                [],
                'Hello tested world!',
            ],
            'auto-escaped output' => [
                '{{trans "Hello %adjective <strong>world</strong>!" adjective="<em>bad</em>"}}',
                [],
                'Hello &lt;em&gt;bad&lt;/em&gt; &lt;strong&gt;world&lt;/strong&gt;!',
            ],
            'unescaped modifier' => [
                '{{trans "Hello %adjective <strong>world</strong>!" adjective="<em>bad</em>"|raw}}',
                [],
                'Hello <em>bad</em> <strong>world</strong>!',
            ],
            'variable replacement' => [
                '{{trans "Hello %adjective world!" adjective="$mood"}}',
                [],
                'Hello happy world!',
                [
                    'mood' => 'happy'
                ],
            ],
        ];
    }

    /**
     * Ensures that the css directive will successfully compile and output contents of a LESS file,
     * as well as supporting loading files from a theme fallback structure.
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoComponentsDir Magento/Email/Model/_files/design
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @dataProvider cssDirectiveDataProvider
     *
     * @param int $templateType
     * @param string $directiveParams
     * @param string $expectedOutput
     */
    public function testCssDirective($templateType, $directiveParams, $expectedOutput)
    {
        /** @var \Magento\Theme\Model\Theme\Registration $registration */
        $registration = $this->objectManager->get(
            \Magento\Theme\Model\Theme\Registration::class
        );
        $registration->register();
        $this->setUpDesignParams();
        $this->model->setStoreId('fixturestore')
            ->setPlainTemplateMode($templateType == TemplateTypesInterface::TYPE_TEXT);

        $output = $this->model->cssDirective(['{{css ' . $directiveParams . '}}', 'css', ' ' . $directiveParams]);

        if ($expectedOutput !== '') {
            $this->assertContains($expectedOutput, $output);
        } else {
            $this->assertSame($expectedOutput, $output);
        }
    }

    /**
     * @return array
     */
    public function cssDirectiveDataProvider()
    {
        return [
            'CSS from theme' => [
                TemplateTypesInterface::TYPE_HTML,
                'file="css/email-1.css"',
                'color: #111'
            ],
            'CSS from parent theme' => [
                TemplateTypesInterface::TYPE_HTML,
                'file="css/email-2.css"',
                'color: #222'
            ],
            'CSS from grandparent theme' => [
                TemplateTypesInterface::TYPE_HTML,
                'file="css/email-3.css"',
                'color: #333'
            ],
            'Missing file parameter' => [
                TemplateTypesInterface::TYPE_HTML,
                '',
                '/* "file" parameter must be specified */'
            ],
            'Plain-text template outputs nothing' => [
                TemplateTypesInterface::TYPE_TEXT,
                'file="css/email-1.css"',
                '',
            ],
            'Empty or missing file' => [
                TemplateTypesInterface::TYPE_HTML,
                'file="css/non-existent-file.css"',
                '/*' . PHP_EOL . ContentProcessorInterface::ERROR_MESSAGE_PREFIX . 'LESS file is empty: ',
            ],
            'File with compilation error results in error message' => [
                TemplateTypesInterface::TYPE_HTML,
                'file="css/file-with-error.css"',
                'variable @non-existent-variable is undefined',
            ],
        ];
    }

    /**
     * Ensures that the inlinecss directive will successfully load and inline CSS to HTML markup,
     * as well as supporting loading files from a theme fallback structure.
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoComponentsDir Magento/Email/Model/_files/design
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture default_store dev/static/sign 0
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
        /** @var \Magento\Theme\Model\Theme\Registration $registration */
        $registration = $this->objectManager->get(
            \Magento\Theme\Model\Theme\Registration::class
        );
        $registration->register();
        $this->setUpDesignParams();

        $this->model->setPlainTemplateMode($plainTemplateMode);
        $this->model->setIsChildTemplate($isChildTemplateMode);

        $appMode = $productionMode ? State::MODE_PRODUCTION : State::MODE_DEVELOPER;
        $this->objectManager->get(\Magento\Framework\App\State::class)->setMode($appMode);

        $this->assertContains($expectedOutput, $this->model->filter($templateText));
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
            'Production mode - File with compilation error results in structurally unmodified markup' => [
                '<html><p></p> {{inlinecss file="css/file-with-error.css"}}</html>',
                '<p></p>',
                true,
            ],
            'Developer mode - File with compilation error results in error message' => [
                '<html><p></p> {{inlinecss file="css/file-with-error.css"}}</html>',
                'CSS inlining error:',
                false,
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoComponentsDir Magento/Email/Model/_files/design
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @dataProvider inlinecssDirectiveThrowsExceptionWhenMissingParameterDataProvider
     *
     * @param string $templateText
     */
    public function testInlinecssDirectiveThrowsExceptionWhenMissingParameter($templateText)
    {
        /** @var \Magento\Theme\Model\Theme\Registration $registration */
        $registration = $this->objectManager->get(
            \Magento\Theme\Model\Theme\Registration::class
        );
        $registration->register();
        $this->setUpDesignParams();

        $this->model->filter($templateText);
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
        $themeCode = 'Vendor_EmailTest/custom_theme';
        $this->model->setDesignParams(
            [
                'area' => Area::AREA_FRONTEND,
                'theme' => $themeCode,
                'locale' => Locale::DEFAULT_SYSTEM_LOCALE,
            ]
        );
    }
}
