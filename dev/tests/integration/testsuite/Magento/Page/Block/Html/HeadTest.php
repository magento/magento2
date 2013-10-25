<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Page
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Page\Block\Html;

class HeadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Page\Block\Html\Head
     */
    private $_block;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\View\DesignInterface')
            ->setDesignTheme('magento_demo', 'frontend');
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\View\LayoutInterface')
            ->createBlock('Magento\Page\Block\Html\Head');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store dev/js/merge_files 0
     * @magentoConfigFixture current_store dev/js/minify_files 0
     */
    public function testGetCssJsHtml()
    {
        $this->_block->addChild(
            'zero.js',
            'Magento\Page\Block\Html\Head\Script',
            array(
                'file' => 'zero.js',
                'properties' => array(
                    'flag_name' => 'nonexisting_condition'
                ),
            )
        );
        $this->_block->addChild(
            'varien/js.js',
            'Magento\Page\Block\Html\Head\Script',
            array(
                'file' => 'varien/js.js',
            )
        );
        $this->_block->addChild(
            'Magento_Bundle::bundle.js',
            'Magento\Page\Block\Html\Head\Script',
            array(
                'file' => 'Magento_Bundle::bundle.js',
            )
        );
        $this->_block->addChild(
            'ui.css',
            'Magento\Page\Block\Html\Head\Css',
            array(
                'file' => 'tiny_mce/themes/advanced/skins/default/ui.css',
            )
        );
        $this->_block->addChild(
            'styles.css',
            'Magento\Page\Block\Html\Head\Css',
            array(
                'file' => 'css/styles.css',
                'properties' => array(
                    'attributes' => 'media="print"'
                )
            )
        );
        $this->_block->addRss('RSS Feed', 'http://example.com/feed.xml');

        $this->_block->addChild(
            'magento-page-head-canonical-link',
            'Magento\Page\Block\Html\Head\Link',
            array(
                'url' => 'http://localhost/index.php/category.html',
                'properties' => array('attributes' => array('rel' => 'next'))
            )
        );

        $this->_block->addChild(
            'varien/form.js',
            'Magento\Page\Block\Html\Head\Script',
            array(
                'file' => 'varien/form.js',
                'properties' => array(
                    'ie_condition' => 'lt IE 7',
                )
            )
        );
        $this->assertEquals(
            '<link rel="alternate" type="application/rss+xml" title="RSS Feed" href="http://example.com/feed.xml" />'
            . "\n"
            . '<script type="text/javascript" src="http://localhost/pub/lib/varien/js.js"></script>' . "\n"
            . '<script type="text/javascript" '
            . 'src="http://localhost/pub/static/frontend/magento_demo/en_US/Magento_Bundle/bundle.js">'
            . '</script>' . "\n"
            . '<link rel="stylesheet" type="text/css" media="all"'
            . ' href="http://localhost/pub/lib/tiny_mce/themes/advanced/skins/default/ui.css" />' . "\n"
            . '<link rel="stylesheet" type="text/css" media="print" '
                . 'href="http://localhost/pub/static/frontend/magento_demo/en_US/css/styles.css" />'
                . "\n"
            . '<link rel="next" href="http://localhost/index.php/category.html" />' . "\n"
            . '<!--[if lt IE 7]>' . "\n"
            . '<script type="text/javascript" src="http://localhost/pub/lib/varien/form.js"></script>' . "\n"
            . '<![endif]-->' . "\n",
            $this->_block->getCssJsHtml()
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetCssJsHtmlBadLink()
    {

        $this->_block->addChild(
            'ui.css',
            'Magento\Page\Block\Html\Head\Css',
            array(
                'file' => 'not_exist_folder/wrong_bad_file2.xyz',
            )
        );
        $this->_block->addChild(
            'jjs',
            'Magento\Page\Block\Html\Head\Script',
            array(
                'file' => 'not_exist_folder/wrong_bad_file.xyz',
            )
        );
        $this->assertEquals(
            '<link rel="stylesheet" type="text/css" media="all"'
                . ' href="http://localhost/index.php/core/index/notfound" />' . "\n"
                . '<script type="text/javascript" src="http://localhost/index.php/core/index/notfound"></script>'
                . "\n",
            $this->_block->getCssJsHtml()
        );
    }

    /**
     * Both existing and non-existent JS and CSS links are specified
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store dev/js/merge_files 0
     * @magentoConfigFixture current_store dev/js/minify_files 0
     */
    public function testGetCssJsHtmlMixedLinks()
    {
        $this->_block->addChild(
            'varien/js.js',
            'Magento\Page\Block\Html\Head\Script',
            array(
                'file' => 'varien/js.js',
            )
        );
        $this->_block->addChild(
            'jjs',
            'Magento\Page\Block\Html\Head\Script',
            array(
                'file' => 'not_exist_folder/wrong_bad_file.xyz',
            )
        );
        $this->_block->addChild(
            'wrong_bad_file2.xyz',
            'Magento\Page\Block\Html\Head\Script',
            array(
                'file' => 'not_exist_folder/wrong_bad_file2.xyz',
                'properties' => array(
                    'ie_condition' => 'lt IE 7',
                )
            )
        );
        $this->_block->addChild(
            'sdsdsd.css',
            'Magento\Page\Block\Html\Head\Css',
            array(
                'file' => 'not_exist_folder/wrong_bad_file2.xyz',
            )
        );

        $this->_block->addChild(
            'css/styles.css',
            'Magento\Page\Block\Html\Head\Css',
            array(
                'file' => 'css/styles.css',
                'properties' => array(
                    'attributes' => 'media="print"'
                )
            )
        );



        $this->assertEquals('<script type="text/javascript" src="http://localhost/pub/lib/varien/js.js"></script>'
            . "\n" . '<script type="text/javascript" src="http://localhost/index.php/core/index/notfound"></script>'
            . "\n" . '<!--[if lt IE 7]>' . "\n"
            . '<script type="text/javascript" src="http://localhost/index.php/core/index/notfound"></script>' . "\n"
            . '<![endif]-->' . "\n"
            . '<link rel="stylesheet" type="text/css" media="all"'
            . ' href="http://localhost/index.php/core/index/notfound" />' . "\n"
            . '<link rel="stylesheet" type="text/css" media="print"'
            . ' href="http://localhost/pub/static/frontend/magento_demo/en_US/css/styles.css" />'
            . "\n", $this->_block->getCssJsHtml());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store dev/js/minify_files 1
     */
    public function testGetCssJsHtmlJsMinified()
    {
        $this->_block->addChild(
            'jjs',
            'Magento\Page\Block\Html\Head\Script',
            array(
                'file' => 'varien/js.js',
            )
        );
        $this->assertStringMatchesFormat(
            '<script type="text/javascript" src="http://localhost/pub/cache/minify/%s_js.min.js"></script>',
            $this->_block->getCssJsHtml()
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store dev/js/minify_files 0
     */
    public function testGetCssJsHtmlJsNotMinified()
    {
        $this->_block->addChild(
            'jjs',
            'Magento\Page\Block\Html\Head\Script',
            array(
                'file' => 'varien/js.js',
            )
        );
        $this->assertSame(
            '<script type="text/javascript" src="http://localhost/pub/lib/varien/js.js"></script>' . "\n",
            $this->_block->getCssJsHtml()
        );
    }

    /**
     * Head link with several attributes
     * @magentoAppIsolation enabled
     */
    public function testGetCssJsHtmlSeveralAttributes()
    {
        $this->_block->addChild(
            'magento-page-head-test-link',
            'Magento\Page\Block\Html\Head\Link',
            array(
                'url' => 'http://localhost/index.php/category.html',
                'properties' => array('attributes' => array(
                    'rel' => 'next', 'attr' => 'value', 'some_other_attr' => 'value2'
                ))
            )
        );

        $this->assertSame(
            '<link rel="next" attr="value" some_other_attr="value2" '
                . 'href="http://localhost/index.php/category.html" />' . "\n",
            $this->_block->getCssJsHtml()
        );
    }

    /**
     * Test getRobots default value
     * @magentoAppIsolation enabled
     */
    public function testGetRobotsDefaultValue()
    {
        $this->assertEquals('INDEX,FOLLOW', $this->_block->getRobots());
    }

    /**
     * Test getRobots
     *
     * @magentoConfigFixture default_store design/search_engine_robots/default_robots INDEX,NOFOLLOW
     * @magentoAppIsolation enabled
     */
    public function testGetRobots()
    {
        $this->assertEquals('INDEX,NOFOLLOW', $this->_block->getRobots());
    }
}
