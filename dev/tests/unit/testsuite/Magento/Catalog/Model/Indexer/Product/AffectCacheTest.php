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

namespace Magento\Catalog\Model\Indexer\Product;


class AffectCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\PageCache\Model\Indexer\Product\RefreshPlugin
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
     *  Set up
     */
    public function setUp()
    {
        $this->subjectMock = $this->getMockForAbstractClass('Magento\Indexer\Model\ActionInterface',
            array(), '', false, true, true, array());
        $this->contextMock = $this->getMock('Magento\Indexer\Model\CacheContext',
            array(), array(), '', false);
        $this->plugin = new \Magento\Catalog\Model\Indexer\Product\AffectCache($this->contextMock);
    }

    /**
     * test beforeExecute
     */
    public function testBeforeExecute()
    {
        $expectedIds = array(1, 2, 3);
        $this->contextMock->expects($this->once())
            ->method('registerEntities')
            ->with($this->equalTo(\Magento\Catalog\Model\Product::ENTITY),
                $this->equalTo($expectedIds))
            ->will($this->returnValue($this->contextMock));
        $actualIds = $this->plugin->beforeExecute($this->subjectMock, $expectedIds);
        $this->assertEquals(array($expectedIds), $actualIds);
    }
}
 