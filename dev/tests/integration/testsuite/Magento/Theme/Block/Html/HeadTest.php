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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Theme\Block\Html;

class HeadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Block\Html\Head
     */
    private $_block;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setAreaCode('frontend');
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\DesignInterface'
        )->setDesignTheme(
            'Magento/blank'
        );
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Theme\Block\Html\Head'
        );
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
            'Magento\Theme\Block\Html\Head\Script',
            array('file' => 'zero.js', 'properties' => array('flag_name' => 'nonexisting_condition'))
        );
        $this->_block->addChild(
            'varien/js.js',
            'Magento\Theme\Block\Html\Head\Script',
            array('file' => 'varien/js.js')
        );
        $this->_block->addChild(
            'Magento_Bundle::bundle.js',
            'Magento\Theme\Block\Html\Head\Script',
            array('file' => 'Magento_Bundle::bundle.js')
        );
        $this->_block->addChild(
            'ui.css',
            'Magento\Theme\Block\Html\Head\Css',
            array('file' => 'tiny_mce/themes/advanced/skins/default/ui.css')
        );
        $this->_block->addChild(
            'styles.css',
            'Magento\Theme\Block\Html\Head\Css',
            array('file' => 'css/styles.css', 'properties' => array('attributes' => 'media="print"'))
        );
        $this->_block->addRss('RSS Feed', 'http://example.com/feed.xml');

        $this->_block->addChild(
            'magento-page-head-canonical-link',
            'Magento\Theme\Block\Html\Head\Link',
            array(
                'url' => 'http://localhost/index.php/category.html',
                'properties' => array('attributes' => array('rel' => 'next'))
            )
        );

        $this->_block->addChild(
            'varien/form.js',
            'Magento\Theme\Block\Html\Head\Script',
            array('file' => 'varien/form.js', 'properties' => array('ie_condition' => 'lt IE 7'))
        );
        $this->assertEquals(
            '<link rel="alternate" type="application/rss+xml" title="RSS Feed" href="http://example.com/feed.xml" />'
            . "\n"
            . '<script type="text/javascript"'
            . ' src="http://localhost/pub/static/frontend/Magento/blank/en_US/varien/js.js"></script>' . "\n"
            . '<script type="text/javascript"'
            . ' src="http://localhost/pub/static/frontend/Magento/blank/en_US/Magento_Bundle/bundle.js">'
            . '</script>' . "\n"
            . '<link rel="stylesheet" type="text/css" media="all"'
            . ' href="http://localhost/pub/static/frontend/Magento/blank/en_US/'
            . 'tiny_mce/themes/advanced/skins/default/ui.css" />' . "\n"
            . '<link rel="stylesheet" type="text/css" media="print" '
                . 'href="http://localhost/pub/static/frontend/Magento/blank/en_US/css/styles.css" />'
                . "\n"
            . '<link rel="next" href="http://localhost/index.php/category.html" />' . "\n"
            . '<!--[if lt IE 7]>' . "\n"
            . '<script type="text/javascript"'
            . ' src="http://localhost/pub/static/frontend/Magento/blank/en_US/varien/form.js"></script>' . "\n"
            . '<![endif]-->' . "\n",
            $this->_block->getCssJsHtml()
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store dev/js/minify_files 1
     */
    public function testGetCssJsHtmlJsMinified()
    {
        $this->_block->addChild('jjs', 'Magento\Theme\Block\Html\Head\Script', array('file' => 'varien/js.js'));
        $this->assertStringMatchesFormat(
            '<script type="text/javascript" src="http://localhost/pub/static/_cache/minified/%s_js.min.js"></script>',
            $this->_block->getCssJsHtml()
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store dev/js/minify_files 0
     */
    public function testGetCssJsHtmlJsNotMinified()
    {
        $this->_block->addChild('jjs', 'Magento\Theme\Block\Html\Head\Script', array('file' => 'varien/js.js'));
        $this->assertSame(
            '<script type="text/javascript"'
                . ' src="http://localhost/pub/static/frontend/Magento/blank/en_US/varien/js.js"></script>' . "\n",
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
            'Magento\Theme\Block\Html\Head\Link',
            array(
                'url' => 'http://localhost/index.php/category.html',
                'properties' => array(
                    'attributes' => array('rel' => 'next', 'attr' => 'value', 'some_other_attr' => 'value2')
                )
            )
        );

        $this->assertSame(
            '<link rel="next" attr="value" some_other_attr="value2" ' .
            'href="http://localhost/index.php/category.html" />' .
            "\n",
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
