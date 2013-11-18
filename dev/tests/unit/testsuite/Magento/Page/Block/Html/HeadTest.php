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

namespace Magento\Page\Block\Html;

class HeadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Page\Block\Html\Head
     */
    protected $_block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_pageAssets;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\ObjectManager');
        $this->_pageAssets = $this->getMock('Magento\Page\Model\Asset\GroupedCollection', array(), array(), '', false);
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $arguments = $objectManagerHelper->getConstructArguments(
            'Magento\Page\Block\Html\Head',
            array('page' => new \Magento\Core\Model\Page($this->_pageAssets), 'objectManager' => $this->_objectManager)
        );
        $this->_block = $objectManagerHelper->getObject('Magento\Page\Block\Html\Head', $arguments);
    }

    protected function tearDown()
    {
        $this->_pageAssets = null;
        $this->_objectManager = null;
        $this->_block = null;
    }

    public function testAddRss()
    {
        $this->_pageAssets->expects($this->once())
            ->method('add')
            ->with(
                'link/http://127.0.0.1/test.rss',
                $this->isInstanceOf('Magento\Core\Model\Page\Asset\Remote'),
                array('attributes' => 'rel="alternate" type="application/rss+xml" title="RSS Feed"')
            );
        $assetRemoteFile = $this->getMock('Magento\Core\Model\Page\Asset\Remote', array(), array(), '', false);
        $this->_objectManager->expects($this->once(''))
            ->method('create')
            ->with('Magento\Core\Model\Page\Asset\Remote')
            ->will($this->returnValue($assetRemoteFile));

        $this->_block->addRss('RSS Feed', 'http://127.0.0.1/test.rss');
    }
}
