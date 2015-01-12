<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Template;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Email\Model\Template\Filter
     */
    protected $_model = null;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
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
        $url = $this->_model->storeDirective(
            ['{{store url="translation/ajax/index"}}', 'store', ' url="translation/ajax/index"']
        );
        $this->assertStringMatchesFormat('http://example.com/index.php/backend/translation/ajax/index/%A', $url);
    }

    public function testEscapehtmlDirective()
    {
        $this->_model->setVariables(
            ['first' => '<p><i>Hello</i> <b>world!</b></p>', 'second' => '<p>Hello <strong>world!</strong></p>']
        );

        $allowedTags = 'i,b';

        $expectedResults = [
            'first' => '&lt;p&gt;<i>Hello</i> <b>world!</b>&lt;/p&gt;',
            'second' => '&lt;p&gt;Hello &lt;strong&gt;world!&lt;/strong&gt;&lt;/p&gt;',
        ];

        foreach ($expectedResults as $varName => $expectedResult) {
            $result = $this->_model->escapehtmlDirective(
                [
                    '{{escapehtml var=$' . $varName . ' allowed_tags=' . $allowedTags . '}}',
                    'escapehtml',
                    ' var=$' . $varName . ' allowed_tags=' . $allowedTags,
                ]
            );
            $this->assertEquals($expectedResult, $result);
        }
    }

    /**
     * @magentoDataFixture Magento/Email/Model/_files/themes.php
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

        $themes = ['frontend' => 'test_default', 'adminhtml' => 'test_default'];
        $design = $objectManager->create('Magento\Core\Model\View\Design', ['themes' => $themes]);
        $objectManager->addSharedInstance($design, 'Magento\Core\Model\View\Design');

        \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea($area);

        $collection = $objectManager->create('Magento\Core\Model\Resource\Theme\Collection');
        $themeId = $collection->getThemeByFullPath('frontend/test_default')->getId();
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
        $objectManager->get('Magento\Framework\View\DesignInterface')->setDesignTheme('test_default');

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
                'E-mail content for frontend/test_default theme',
            ],
            'area parameter - frontend' => [
                'adminhtml',
                'handle="email_template_test_handle" area="frontend"',
                'E-mail content for frontend/test_default theme',
            ],
            'area parameter - backend' => [
                'frontend',
                'handle="email_template_test_handle" area="adminhtml"',
                'E-mail content for adminhtml/test_default theme',
            ],
            'custom parameter' => [
                'frontend',
                'handle="email_template_test_handle" template="Magento_Core::sample_email_content_custom.phtml"',
                'Custom E-mail content for frontend/test_default theme',
            ],
        ];
        return $result;
    }
}
