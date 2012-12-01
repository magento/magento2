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
 * @package     Mage_Page
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Page_Block_Html_HeadTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Page_Block_Html_Head
     */
    private $_block = null;

    public static function setUpBeforeClass()
    {
        Mage::getDesign()->setDesignTheme('default/demo', 'frontend');
    }

    protected function setUp()
    {
        $this->_block = Mage::app()->getLayout()->createBlock('Mage_Page_Block_Html_Head');
    }

    protected function tearDown()
    {
        $this->_block = null;
    }

    public function testAddCss()
    {
        $this->assertEmpty($this->_block->getItems());
        $this->_block->addCss('test.css');
        $this->assertEquals(array('css/test.css' => array(
                'type'   => 'css',
                'name'   => 'test.css',
                'params' => 'rel="stylesheet" type="text/css" media="all"',
                'if'     => null,
                'cond'   => null,
            )), $this->_block->getItems()
        );
    }

    /**
     * @expectedException Magento_Exception
     */
    public function testAddCssException()
    {
        $this->_block->addCss('');
    }

    public function testGetCssJsHtmlBadLink()
    {
        $this->_block->addJs('varien/js.js')
            ->addJs('varien/form.js', '', 'lt IE 7')
            ->addCss('not_exist_folder/wrong_bad_file.xyz')
            ->addCss('css/styles.css', '   media="print" ')
            ->addJs('not_exist_folder/wrong_bad_file2.xyz');

        $this->assertEquals('<script type="text/javascript" src="http://localhost/index.php/core/index/notfound">'
            . '</script>' . "\n" . '<!--[if lt IE 7]>' . "\n"
            . '<script type="text/javascript" src="http://localhost/pub/lib/varien/form.js"></script>' . "\n"
            . '<![endif]-->' . "\n" . '<link rel="stylesheet" type="text/css" media="all"'
            . ' href="http://localhost/index.php/core/index/notfound" />' . "\n"
            . '<link rel="stylesheet" type="text/css" media="print"'
            . ' href="http://localhost/pub/media/theme/frontend/default/demo/en_US/css/styles.css" />'
            . "\n", $this->_block->getCssJsHtml());
    }

    public function testGetCssJsHtml()
    {
        $this->_block->addJs('zero.js', '', null, 'nonexisting_condition')
            ->addJs('varien/js.js')
            ->addJs('Mage_Bundle::bundle.js')
            ->addCss('tiny_mce/themes/advanced/skins/default/ui.css')
            ->addCss('css/styles.css', '   media="print" ')
            ->addRss('RSS Feed', 'http://example.com/feed.xml')
            ->addLinkRel('next', 'http://example.com/page1.html')
            ->addJs('varien/form.js', '', 'lt IE 7')
        ;
        $this->assertEquals(
            '<script type="text/javascript" src="http://localhost/pub/lib/varien/js.js"></script>' . "\n"
            . '<script type="text/javascript" '
            . 'src="http://localhost/pub/media/theme/frontend/default/demo/en_US/Mage_Bundle/bundle.js">'
            . '</script>' . "\n"
            . '<link rel="stylesheet" type="text/css" media="all"'
            . ' href="http://localhost/pub/lib/tiny_mce/themes/advanced/skins/default/ui.css" />' . "\n"
            . '<link rel="stylesheet" type="text/css" media="print" '
                . 'href="http://localhost/pub/media/theme/frontend/default/demo/en_US/css/styles.css" />'
                . "\n"
            . '<link rel="alternate" type="application/rss+xml" title="RSS Feed" href="http://example.com/feed.xml" />'
                . "\n"
            . '<link rel="next" href="http://example.com/page1.html" />' . "\n"
            . '<!--[if lt IE 7]>' . "\n"
            . '<script type="text/javascript" src="http://localhost/pub/lib/varien/form.js"></script>' . "\n"
            . '<![endif]-->' . "\n",
            $this->_block->getCssJsHtml()
        );
    }

    /**
     * Test getRobots default value
     */
    public function testGetRobotsDefaultValue()
    {
        $this->assertEquals('INDEX,FOLLOW', $this->_block->getRobots());
    }

    /**
     * Test getRobots
     *
     * @magentoConfigFixture default_store design/search_engine_robots/default_robots INDEX,NOFOLLOW
     */
    public function testGetRobots()
    {
        $this->assertEquals('INDEX,NOFOLLOW', $this->_block->getRobots());
    }
}
