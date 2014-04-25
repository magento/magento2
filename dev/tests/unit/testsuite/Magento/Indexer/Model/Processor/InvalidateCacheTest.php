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

namespace Magento\Indexer\Model\Processor;


class InvalidateCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\Model\Processor\InvalidateCache
     */
    protected $plugin;

    /**
     * @var \Magento\Indexer\Model\CacheContext|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Indexer\Model\ActionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \Magento\Framework\Event\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\Module\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleManager;

    /**
     * Set up
     */
    public function setUp()
    {
        $this->subjectMock = $this->getMock('Magento\Indexer\Model\Processor',
            array(), array(), '', false);
        $this->contextMock = $this->getMock('Magento\Indexer\Model\CacheContext',
            array(), array(), '', false);
        $this->eventManagerMock = $this->getMock('Magento\Framework\Event\Manager',
            array(), array(), '', false);
        $this->moduleManager = $this->getMock('Magento\Framework\Module\Manager',
            array(), array(), '', false);
        $this->plugin = new \Magento\Indexer\Model\Processor\InvalidateCache(
            $this->contextMock, $this->eventManagerMock, $this->moduleManager);
    }

    /**
     * Test afterUpdateMview with enabled PageCache module
     */
    public function testAfterUpdateMviewPageCacheEnabled()
    {
        $this->moduleManager->expects($this->once())
            ->method('isEnabled')
            ->with($this->equalTo('Magento_PageCache'))
            ->will($this->returnValue(true));
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with($this->equalTo('clean_cache_after_reindex'),
                $this->equalTo(array('object' => $this->contextMock)));
        $actualResult = $this->plugin->afterUpdateMview($this->subjectMock);
        $this->assertNull($actualResult);
    }

    /**
     * afterUpdateMview with disabled PageCache module
     */
    public function testAfterUpdateMviewPageCacheDisabled()
    {
        $this->moduleManager->expects($this->once())
            ->method('isEnabled')
            ->with($this->equalTo('Magento_PageCache'))
            ->will($this->returnValue(false));
        $this->eventManagerMock->expects($this->never())
            ->method('dispatch');
        $actualResult = $this->plugin->afterUpdateMview($this->subjectMock);
        $this->assertNull($actualResult);
    }
}
