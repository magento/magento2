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
 * @package     Magento_Backend
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Backend\Model\Url
 */
namespace Magento\Backend\Block\Widget;

class GridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    /**
     * @covers \Magento\Backend\Block\Widget\Grid::addRssList
     * @covers \Magento\Backend\Block\Widget\Grid::clearRss
     * @covers \Magento\Backend\Block\Widget\Grid::getRssLists
     * @dataProvider addGetClearRssDataProvider
     */
    public function testAddGetClearRss($isUseStoreInUrl, $setStoreCount)
    {
        $helperMock = $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false);

        $urlMock = $this->getMock('Magento\Core\Model\Url', array(), array(), '', false);
        $urlMock->expects($this->at($setStoreCount))
            ->method('setStore');
        $urlMock->expects($this->any())
            ->method('getUrl')
            ->will($this->returnValue('some_url'));

        $storeMock = $this->getMock('Magento\Core\Model\Store', array(), array(), '', false);
        $storeMock->expects($this->any())
            ->method('isUseStoreInUrl')
            ->will($this->returnValue($isUseStoreInUrl));

        $storeManagerMock = $this->getMock('Magento\Core\Model\StoreManager', array(), array(), '', false);

        $appMock = $this->getMock('Magento\Core\Model\App', array(), array(), '', false);
        $appMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($storeMock));
        $appMock->expects($this->any())
            ->method('getDefaultStoreView')
            ->will($this->returnValue($storeMock));

        $contextMock = $this->getMock('\Magento\Backend\Block\Template\Context', array(), array(), '', false);
        $contextMock->expects($this->any())
            ->method('getStoreManager')
            ->will($this->returnValue($storeManagerMock));
        $contextMock->expects($this->any())
            ->method('getApp')
            ->will($this->returnValue($appMock));

        $block = new \Magento\Backend\Block\Widget\Grid($helperMock, $contextMock, $storeManagerMock, $urlMock);

        $this->assertFalse($block->getRssLists());

        $block->addRssList('some_url', 'some_label');
        $elements = $block->getRssLists();
        $element = reset($elements);
        $this->assertEquals('some_url', $element->getUrl());
        $this->assertEquals('some_label', $element->getLabel());

        $block->clearRss();
        $this->assertFalse($block->getRssLists());
    }

    /**
     * @see self::testAddGetClearRss()
     * @return array
     */
    public function addGetClearRssDataProvider()
    {
         return array(
            array(true, 1),
            array(false, 0),
         );
    }
}
