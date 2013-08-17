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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Page_Block_Html_HeadTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Page_Block_Html_Head
     */
    protected $_block;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_pageAssets;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento_ObjectManager');
        $this->_pageAssets = $this->getMock('Mage_Page_Model_Asset_GroupedCollection', array(), array(), '', false);
        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $arguments = $objectManagerHelper->getConstructArguments(
            'Mage_Page_Block_Html_Head',
            array('page' => new Mage_Core_Model_Page($this->_pageAssets), 'objectManager' => $this->_objectManager)
        );
        $this->_block = $objectManagerHelper->getObject('Mage_Page_Block_Html_Head', $arguments);
    }

    protected function tearDown()
    {
        $this->_pageAssets = null;
        $this->_objectManager = null;
        $this->_block = null;
    }

    public function testAddCss()
    {
        $this->_pageAssets->expects($this->once())
            ->method('add')
            ->with(
                Mage_Core_Model_View_Publisher::CONTENT_TYPE_CSS . '/test.css',
                $this->isInstanceOf('Mage_Core_Model_Page_Asset_ViewFile')
            );
        $assetViewFile = $this->getMock('Mage_Core_Model_Page_Asset_ViewFile', array(), array(), '', false);
        $this->_objectManager->expects($this->once(''))
            ->method('create')
            ->with('Mage_Core_Model_Page_Asset_ViewFile')
            ->will($this->returnValue($assetViewFile));
        $this->_block->addCss('test.css');
    }

    public function testAddJs()
    {
        $this->_pageAssets->expects($this->once())
            ->method('add')
            ->with(
                Mage_Core_Model_View_Publisher::CONTENT_TYPE_JS . '/test.js',
                $this->isInstanceOf('Mage_Core_Model_Page_Asset_ViewFile')
            );
        $assetViewFile = $this->getMock('Mage_Core_Model_Page_Asset_ViewFile', array(), array(), '', false);
        $this->_objectManager->expects($this->once(''))
            ->method('create')
            ->with('Mage_Core_Model_Page_Asset_ViewFile')
            ->will($this->returnValue($assetViewFile));
        $this->_block->addJs('test.js');
    }

    public function testAddRss()
    {
        $this->_pageAssets->expects($this->once())
            ->method('add')
            ->with(
                'link/http://127.0.0.1/test.rss',
                $this->isInstanceOf('Mage_Core_Model_Page_Asset_Remote'),
                array('attributes' => 'rel="alternate" type="application/rss+xml" title="RSS Feed"')
            );
        $assetRemoteFile = $this->getMock('Mage_Core_Model_Page_Asset_Remote', array(), array(), '', false);
        $this->_objectManager->expects($this->once(''))
            ->method('create')
            ->with('Mage_Core_Model_Page_Asset_Remote')
            ->will($this->returnValue($assetRemoteFile));

        $this->_block->addRss('RSS Feed', 'http://127.0.0.1/test.rss');
    }

    public function testAddLinkRel()
    {
        $this->_pageAssets->expects($this->once())
            ->method('add')
            ->with(
                'link/http://127.0.0.1/',
                $this->isInstanceOf('Mage_Core_Model_Page_Asset_Remote'),
                array('attributes' => 'rel="rel"')
            );
        $assetRemoteFile = $this->getMock('Mage_Core_Model_Page_Asset_Remote', array(), array(), '', false);
        $this->_objectManager->expects($this->once(''))
            ->method('create')
            ->with('Mage_Core_Model_Page_Asset_Remote')
            ->will($this->returnValue($assetRemoteFile));
        $this->_block->addLinkRel('rel', 'http://127.0.0.1/');
    }

    public function testRemoveItem()
    {
        $this->_pageAssets->expects($this->once())
            ->method('remove')
            ->with('css/test.css');
        $this->_block->removeItem('css', 'test.css');
    }

    /**
     * @dataProvider addMetaTagProvider
     */
    public function testAddMetaTag($metaTag, $content, $expected)
    {
        $this->_block->setDescription('description');
        $this->_block->setKeywords('keywords');
        $this->_block->setRobots('robots');
        $this->_block->addMetaTag($metaTag, $content);
        $this->assertEquals($expected, $this->_block->getMetaTags());
    }

    public function addMetaTagProvider()
    {
        return array(
            array(
                'metaTag' => 'test_name',
                'content' => 'test_content',
                'expected' => array(
                    array('name' => 'description', 'content' => 'description'),
                    array('name' => 'keywords', 'content' => 'keywords'),
                    array('name' => 'robots', 'content' => 'robots'),
                    array('name' => 'test_name', 'content' => 'test_content')
                )
            ),
            array(
                'metaTag' => array('name' => 'test_name', 'content' => 'test_content'),
                'content' => null,
                'expected' => array(
                    array('name' => 'description', 'content' => 'description'),
                    array('name' => 'keywords', 'content' => 'keywords'),
                    array('name' => 'robots', 'content' => 'robots'),
                    array('name' => 'test_name', 'content' => 'test_content')
                )
            ),
            array(
                'metaTag' => 'test_name',
                'content' => null,
                'expected' => array(
                    array('name' => 'description', 'content' => 'description'),
                    array('name' => 'keywords', 'content' => 'keywords'),
                    array('name' => 'robots', 'content' => 'robots')
                )
            ),
            array(
                'metaTag' => array('name' => 'test_name', 'content' => null),
                'content' => null,
                'expected' => array(
                    array('name' => 'description', 'content' => 'description'),
                    array('name' => 'keywords', 'content' => 'keywords'),
                    array('name' => 'robots', 'content' => 'robots')
                )
            ),
        );
    }

    public function testGetMetaTagHtml()
    {
        $this->_block->setDescription('description');
        $this->_block->setKeywords('keywords');
        $this->_block->setRobots('robots');
        $this->_block->addMetaTag('test_name', 'test_content');
        $expectedHtml = array(
            '<meta name="description" content="description"/>',
            '<meta name="keywords" content="keywords"/>',
            '<meta name="robots" content="robots"/>',
            '<meta name="test_name" content="test_content"/>'
        );
        $this->assertEquals(implode("\n", $expectedHtml), $this->_block->getMetaTagHtml());
    }
}
