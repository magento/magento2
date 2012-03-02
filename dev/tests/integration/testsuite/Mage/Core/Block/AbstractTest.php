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
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Block_AbstractTestAbstract extends Mage_Core_Block_Abstract
{
}

/**
 * @group module:Mage_Core
 */
class Mage_Core_Block_AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Block_Abstract
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = new Mage_Core_Block_AbstractTestAbstract;
    }

    public function testGetRequest()
    {
        $this->assertInstanceOf('Mage_Core_Controller_Request_Http', $this->_block->getRequest());
    }

    public function testSetGetParentBlock()
    {
        $this->assertEmpty($this->_block->getParentBlock());
        $parentBlock = new Mage_Core_Block_Text;
        $this->_block->setParentBlock($parentBlock);
        $this->assertSame($parentBlock, $this->_block->getParentBlock());
    }

    public function testSetGetIsAnonymous()
    {
        $this->assertFalse($this->_block->getIsAnonymous());
        $this->_block->setIsAnonymous(true);
        $this->assertTrue($this->_block->getIsAnonymous());
    }

    public function testSetGetAnonSuffix()
    {
        $this->assertEquals('', $this->_block->getAnonSuffix());
        $this->_block->setAnonSuffix('suffix');
        $this->assertEquals('suffix', $this->_block->getAnonSuffix());
    }

    public function testGetSetBlockAlias()
    {
        $this->assertEmpty($this->_block->getBlockAlias());
        $this->_block->setBlockAlias('alias');
        $this->assertEquals('alias', $this->_block->getBlockAlias());
    }

    public function testSetGetNameInLayout()
    {
        // basic setting/getting
        $this->assertEmpty($this->_block->getNameInLayout());
        $name = uniqid('name');
        $this->_block->setNameInLayout($name);
        $this->assertEquals($name, $this->_block->getNameInLayout());

        // setting second time, along with the layout
        $layout = Mage::app()->getLayout();
        $layout->createBlock('Mage_Core_Block_Template', $name);
        $block = $layout->getBlock($name);
        $this->assertInstanceOf('Mage_Core_Block_Abstract', $block);
        $block->setNameInLayout($name);
        $this->assertInstanceOf('Mage_Core_Block_Abstract', $layout->getBlock($name));
    }

    /**
     * @covers Mage_Core_Block_Abstract::getSortedChildren
     * @covers Mage_Core_Block_Abstract::insert
     */
    public function testGetSortedChildren()
    {
        $cloneOne = clone $this->_block;
        $cloneOne->setNameInLayout('block.clone1');
        $cloneTwo = clone $this->_block;
        $cloneTwo->setNameInLayout('block.clone2');
        $cloneThree = clone $this->_block;
        $cloneThree->setNameInLayout('block.clone3');
        $cloneFour = clone $this->_block;
        $cloneFour->setNameInLayout('block.clone4');

        $this->_block->insert($cloneOne, '', false); // add one block
        $this->_block->insert($cloneTwo, 'block.clone1', false); // add second to the 1st position
        $this->_block->insert($cloneThree, 'block.clone1', false); // add third to the 2nd position
        $this->_block->insert($cloneFour, 'block.clone3', true); // add fourth block to the 3rd position

        $this->assertEquals(array(
            'block.clone2', 'block.clone3', 'block.clone4', 'block.clone1'
        ), $this->_block->getSortedChildren());
    }

    public function testSetAttribute()
    {
        $this->assertEmpty($this->_block->getSomeValue());
        $this->_block->setAttribute('some_value', 'value');
        $this->assertEquals('value', $this->_block->getSomeValue());
    }

    public function testSetGetUnsetChild()
    {
        $layout = Mage::app()->getLayout();
        $this->_block->setLayout($layout);

        // regular block
        $blockOne = new Mage_Core_Block_Template;
        $nameOne = uniqid('block.');
        $blockOne->setNameInLayout($nameOne);
        $layout->setBlock($nameOne, $blockOne);
        $this->_block->setChild('block1', $blockOne);
        $this->assertSame($blockOne, $this->_block->getChild('block1'));

        // block factory name
        $blockTwo = new Mage_Core_Block_Template;
        $blockTwo->setLayout($layout);
        $blockTwo->setChild('block2', $nameOne);
        $this->assertSame($blockOne, $blockTwo->getChild('block2'));

        // anonymous block
        $blockThree = new Mage_Core_Block_Template;
        $blockThree->setIsAnonymous(true);
        $this->_block->setChild('block3', $blockThree);
        $this->assertSame($blockThree, $this->_block->getChild('block3'));

        // unset
        $this->_block->unsetChild('block3');
        $this->assertNotSame($blockThree, $this->_block->getChild('block3'));
        $this->_block->insert($blockOne, '', true, 'block1');
        $this->assertContains($nameOne, $this->_block->getSortedChildren());
        $this->_block->unsetChild('block1');
        $this->assertNotSame($blockOne, $this->_block->getChild('block1'));
        $this->assertNotContains($nameOne, $this->_block->getSortedChildren());
    }

    public function testUnsetCallChild()
    {
        $blockOne = new Mage_Core_Block_Template;
        $blockOne->setSomeValue(true);
        $this->_block->setChild('block1', $blockOne);
        $this->assertSame($blockOne, $this->_block->getChild('block1'));
        $this->_block->unsetCallChild('block1', 'getSomeValue', true, array());
        $this->assertNotSame($blockOne, $this->_block->getChild('block1'));
    }

    /**
     * @covers Mage_Core_Block_Abstract::unsetChildren
     * @covers Mage_Core_Block_Abstract::getChild
     */
    public function testUnsetChildren()
    {
        $this->assertEquals(array(), $this->_block->getChild());
        $blockOne = new Mage_Core_Block_Template;
        $blockTwo = new Mage_Core_Block_Template;
        $this->_block->setChild('block1', $blockOne);
        $this->_block->setChild('block2', $blockTwo);
        $this->assertSame($blockOne, $this->_block->getChild('block1'));
        $this->assertSame($blockTwo, $this->_block->getChild('block2'));
        $this->_block->unsetChildren();
        $this->assertEquals(array(), $this->_block->getChild());
    }

    /**
     * @covers Mage_Core_Block_Abstract::getChildHtml
     * @covers Mage_Core_Block_Abstract::getChildChildHtml
     */
    public function testGetChildHtml()
    {
        $blockOne = new Mage_Core_Block_Text;
        $blockOne->setText('one')->setNameInLayout(uniqid('block.one.'));
        $blockTwo = new Mage_Core_Block_Text;
        $blockTwo->setText('two')->setNameInLayout(uniqid('block.two.'));
        $this->_block->insert($blockTwo, '', false, 'block2'); // make block2 1st
        $this->_block->insert($blockOne, '', false, 'block1'); // make block1 1st

        $this->assertEquals('one', $this->_block->getChildHtml('block1'));
        $this->assertEquals('two', $this->_block->getChildHtml('block2'));

        // unsorted children will render in the order they were added
        $this->assertEquals('twoone', $this->_block->getChildHtml());

        // hack: rendering sorted children requires layout
        $layout = new Mage_Core_Model_Layout;
        $this->_block->setLayout($layout);
        $blockOne->setLayout($layout);
        $layout->setBlock($blockOne->getNameInLayout(), $blockOne);
        $blockTwo->setLayout($layout);
        $layout->setBlock($blockTwo->getNameInLayout(), $blockTwo);

        // sorted will render in the designated order
        $this->assertEquals('onetwo', $this->_block->getChildHtml('', true, true));

        // getChildChildHtml
        $blockTwo->setChild('block11', $blockOne);
        $this->assertEquals('one', $this->_block->getChildChildHtml('block2'));
        $this->assertEquals('', $this->_block->getChildChildHtml(''));
        $this->assertEquals('', $this->_block->getChildChildHtml('block3'));
    }

    /**
     * @covers Mage_Core_Block_Abstract::getSortedChildBlocks
     * @covers Mage_Core_Block_Abstract::append
     */
    public function testGetSortedChildBlocks()
    {
        list($blocks, $names) = $this->_createSampleBlocks(2);
        $this->_block->append($blocks[0], 'block1')->append($blocks[1], 'block2');
        $result = $this->_block->getSortedChildBlocks();
        $this->assertArrayHasKey($names[0], $result);
        $this->assertArrayHasKey($names[1], $result);
        $this->assertSame($names[0], key($result));
        $this->assertSame($blocks[0], $result[$names[0]]);
        $this->assertSame($blocks[1], $result[$names[1]]);
    }

    /**
     * @covers Mage_Core_Block_Abstract::insert
     * @see testGetSortedChildren()
     */
    public function testInsert()
    {
        // invalid block from layout
        $blockZero = new Mage_Core_Block_Template;
        $blockZero->setLayout(Mage::app()->getLayout());
        $this->assertInstanceOf('Mage_Core_Block_Abstract', $blockZero->insert(uniqid('block.')));

        // anonymous block
        $blockOne = new Mage_Core_Block_Template;
        $blockOne->setIsAnonymous(true);
        $this->_block->insert($blockOne);
        $this->assertContains('.child0', $this->_block->getSortedChildren());

        // block with alias, to the last position
        $blockTwo = new Mage_Core_Block_Template;
        $blockTwo->setNameInLayout('block.two');
        $this->_block->insert($blockTwo, '', true, 'block_two');
        $this->assertContains('block.two', $this->_block->getSortedChildren());
        $this->assertSame($blockTwo, $this->_block->getChild('block_two'));

        // unknown sibling, to the 1st position
        $blockThree = new Mage_Core_Block_Template;
        $blockThree->setNameInLayout('block.three');
        $this->_block->insert($blockThree, 'wrong_sibling', false, 'block_three');
        $this->assertContains('block.three', $this->_block->getSortedChildren());
        $this->assertSame(0, array_search('block.three', $this->_block->getSortedChildren()));

        $blockFour = new Mage_Core_Block_Template;
        $blockFour->setNameInLayout('block.four');
        $this->_block->insert($blockFour, 'wrong_sibling', true, 'block_four');
        $this->assertContains('block.four', $this->_block->getSortedChildren());
        $this->assertSame(3, array_search('block.four', $this->_block->getSortedChildren()));
    }

    /**
     * @covers Mage_Core_Block_Abstract::addToChildGroup
     * @covers Mage_Core_Block_Abstract::getChildGroup
     */
    public function testAddToChildGroup()
    {
        list($blocks, ) = $this->_createSampleBlocks(2);
        $this->_block->append($blocks[0], 'block1')->append($blocks[1], 'block2');

        // addToChildGroup()
        $this->assertEquals(array(), $this->_block->getChildGroup('group'));
        $this->_block->addToChildGroup('group', $blocks[0]);
        $this->_block->addToChildGroup('group', $blocks[1]);

        // getChildGroup() without callback
        $group = $this->_block->getChildGroup('group');
        $this->assertEquals(array('block1' => $blocks[0], 'block2' => $blocks[1]), $group);

        // getChildGroup() with callback and skipping empty results
        $group = $this->_block->getChildGroup('group', 'getChildHtml');
        $this->assertEquals(array(), $group);

        // getChildGroup() with callback and not skipping empty results
        $group = $this->_block->getChildGroup('group', 'getChildHtml', false);
        $this->assertEquals(array('block1' => '', 'block2' => ''), $group);
    }

    public function testAddToParentGroup()
    {
        list($blocks, ) = $this->_createSampleBlocks(2);
        $this->_block->append($blocks[0], 'block1')->append($blocks[1], 'block2');
        $blocks[0]->addToParentGroup('group');
        $blocks[1]->addToParentGroup('group');
        $group = $this->_block->getChildGroup('group');
        $this->assertArrayHasKey('block1', $group);
        $this->assertArrayHasKey('block2', $group);
        $this->assertSame($group['block1'], $blocks[0], 'The same instance is expected.');
        $this->assertSame($group['block2'], $blocks[1], 'The same instance is expected.');
    }

    public function testGetChildData()
    {
        $block = new Mage_Core_Block_Template();
        $block->setSomeValue('value');
        $this->_block->setChild('block1', $block);
        $this->assertEquals(array('some_value' => 'value'), $this->_block->getChildData('block1'));
        $this->assertEquals('value', $this->_block->getChildData('block1', 'some_value'));
        $this->assertNull($this->_block->getChildData('unknown_block'));
    }

    public function testSetFrameTags()
    {
        $block = new Mage_Core_Block_Text;
        $block->setText('text');

        $block->setFrameTags('p');
        $this->assertEquals('<p>text</p>', $block->toHtml());

        $block->setFrameTags('p class="note"', '/p');
        $this->assertEquals('<p class="note">text</p>', $block->toHtml());

        $block->setFrameTags('non-wellformed tag', 'closing tag');
        $this->assertEquals('<non-wellformed tag>text<closing tag>', $block->toHtml());
    }

    public function testGetUrl()
    {
        $base = 'http://localhost/index.php/';
        $withRoute = "{$base}catalog/product/view/id/10/";
        $this->assertEquals($base, $this->_block->getUrl());
        $this->assertEquals($withRoute, $this->_block->getUrl('catalog/product/view', array('id' => 10)));
    }

    /**
     * @covers Mage_Core_Block_Abstract::getUrlBase64
     * @covers Mage_Core_Block_Abstract::getUrlEncoded
     */
    public function testGetUrlBase64()
    {
        foreach (array('getUrlBase64', 'getUrlEncoded') as $method) {
            $base = 'http://localhost/index.php/';
            $withRoute = "{$base}catalog/product/view/id/10/";

            $encoded = $this->_block->$method();
            $this->assertEquals(Mage::helper('Mage_Core_Helper_Data')->urlDecode($encoded), $base);
            $encoded = $this->_block->$method('catalog/product/view', array('id' => 10));
            $this->assertEquals(Mage::helper('Mage_Core_Helper_Data')->urlDecode($encoded), $withRoute);
        }
    }

    public function testGetSkinUrl()
    {
        $this->assertStringStartsWith('http://localhost/pub/media/skin/frontend/', $this->_block->getSkinUrl());
        $this->assertStringEndsWith('css/styles.css', $this->_block->getSkinUrl('css/styles.css'));
    }

    public function testGetSetMessagesBlock()
    {
        // get one from layout
        $this->_block->setLayout(new Mage_Core_Model_Layout);
        $this->assertInstanceOf('Mage_Core_Block_Messages', $this->_block->getMessagesBlock());

        // set explicitly
        $messages = new Mage_Core_Block_Messages;
        $this->_block->setMessagesBlock($messages);
        $this->assertSame($messages, $this->_block->getMessagesBlock());
    }

    public function testGetHelper()
    {
        $this->_block->setLayout(new Mage_Core_Model_Layout);
        $this->assertInstanceOf('Mage_Core_Block_Text', $this->_block->getHelper('Mage_Core_Block_Text'));
    }

    public function testHelper()
    {
        // without layout
        $this->assertInstanceOf('Mage_Core_Helper_Data', $this->_block->helper('Mage_Core_Helper_Data'));

        // with layout
        $this->_block->setLayout(new Mage_Core_Model_Layout);
        $helper = $this->_block->helper('Mage_Core_Helper_Data');

        try {
            $this->assertInstanceOf('Mage_Core_Helper_Data', $helper);
            $this->assertInstanceOf('Mage_Core_Model_Layout', $helper->getLayout());
            /* Helper is a 'singleton', so assigned layout may affect further helper usage */
            $helper->setLayout(null);
        } catch (Exception $e) {
            $helper->setLayout(null);
            throw $e;
        }
    }

    public function testFormatDate()
    {
        $helper = Mage::helper('Mage_Core_Helper_Data');
        $this->assertEquals($helper->formatDate(), $this->_block->formatDate());
    }

    public function testFormatTime()
    {
        $helper = Mage::helper('Mage_Core_Helper_Data');
        $this->assertEquals($helper->formatTime(), $this->_block->formatTime());
    }

    public function testGetModuleName()
    {
        $this->assertEquals('Mage_Core', $this->_block->getModuleName());
        $this->assertEquals('Mage_Core', $this->_block->getData('module_name'));
    }

    public function test__()
    {
        $str = uniqid();
        $this->assertEquals($str, $this->_block->__($str));
    }

    /**
     * @dataProvider escapeHtmlDataProvider
     */
    public function testEscapeHtml($data, $expected)
    {
        $actual = $this->_block->escapeHtml($data);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function escapeHtmlDataProvider()
    {
        return array(
            'array data' => array(
                'data' => array('one', '<two>three</two>'),
                'expected' => array('one', '&lt;two&gt;three&lt;/two&gt;')
            ),
            'string data conversion' => array(
                'data' => '<two>three</two>',
                'expected' => '&lt;two&gt;three&lt;/two&gt;'
            ),
            'string data no conversion' => array(
                'data' => 'one',
                'expected' => 'one'
            )
        );
    }

    public function testStripTags()
    {
        $str = '<p>text</p>';
        $this->assertEquals('text', $this->_block->stripTags($str));
    }

    public function testEscapeUrl()
    {
        $url = 'http://example.com/?wsdl=1';
        $this->assertEquals($url, $this->_block->escapeUrl($url));
    }

    public function testJsQuoteEscape()
    {
        $script = "var s = 'text';";
        $this->assertEquals('var s = \\\'text\\\';', $this->_block->jsQuoteEscape($script));
    }

    public function testCountChildren()
    {
        $this->assertEquals(0, $this->_block->countChildren());
        $this->_block->setChild('block1', new Mage_Core_Block_Text)
            ->setChild('block2', new Mage_Core_Block_Text)
            ->setChild('block3', new Mage_Core_Block_Text)
        ;
        $this->assertEquals(3, $this->_block->countChildren());
    }

    public function testGetCacheKeyInfo()
    {
        $name = uniqid('block.');
        $block = new Mage_Core_Block_Text;
        $block->setNameInLayout($name);
        $this->assertEquals(array($name), $block->getCacheKeyInfo());
    }

    public function testGetCacheKey()
    {
        $name = uniqid('block.');
        $block = new Mage_Core_Block_Text;
        $block->setNameInLayout($name);
        $key = $block->getCacheKey();
        $this->assertNotEmpty($key);
        $this->assertNotEquals('key', $key);
        $this->assertNotEquals($name, $key);

        $block->setCacheKey('key');
        $this->assertEquals('key', $block->getCacheKey());
    }

    public function testGetCacheTags()
    {
        $this->assertContains(Mage_Core_Block_Abstract::CACHE_GROUP, $this->_block->getCacheTags());

        $this->_block->setCacheTags(array('one', 'two'));
        $tags = $this->_block->getCacheTags();
        $this->assertContains(Mage_Core_Block_Abstract::CACHE_GROUP, $tags);
        $this->assertContains('one', $tags);
        $this->assertContains('two', $tags);
    }

    public function testGetCacheLifetime()
    {
        $this->assertNull($this->_block->getCacheLifetime());
        $this->_block->setCacheLifetime(1800);
        $this->assertEquals(1800, $this->_block->getCacheLifetime());
    }

    /**
     * App isolation is enabled, because config options object is affected
     *
     * @magentoAppIsolation enabled
     */
    public function testGetVar()
    {
        Mage::getConfig()->getOptions()->setDesignDir(dirname(__DIR__) . '/Model/_files/design');
        Mage::getDesign()->setDesignTheme('test/default/default');
        $this->assertEquals('Core Value1', $this->_block->getVar('var1'));
        $this->assertEquals('value1', $this->_block->getVar('var1', 'Namespace_Module'));
        $this->_block->setModuleName('Namespace_Module');
        $this->assertEquals('value1', $this->_block->getVar('var1'));
        $this->assertEquals(false, $this->_block->getVar('unknown_var'));
    }

    /**
     * Create <N> sample blocks
     *
     * @param int $qty
     * @param bool $withLayout
     * @param string $className
     * @return array
     */
    protected function _createSampleBlocks($qty, $withLayout = true, $className = 'Mage_Core_Block_Template')
    {
        $blocks = array(); $names = array();
        $layout = false;
        if ($withLayout) {
            $layout = new Mage_Core_Model_Layout;
            $this->_block->setLayout($layout);
        }
        for ($i = 0; $i < $qty; $i++) {
            $block = new $className;
            $name = uniqid('block.');
            $block->setNameInLayout($name);
            $blocks[] = $block;
            $names[] = $name;
            if ($layout) {
                $block->setLayout($layout);
                $layout->setBlock($name, $block);
            }
        }
        return array($blocks, $names);
    }
}
