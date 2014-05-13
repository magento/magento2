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
namespace Magento\CatalogInventory\Model\Stock;

/**
 * Class ItemTest
 */
class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Stock\Item
     */
    protected $item;

    /**
     * @var \Magento\CatalogInventory\Model\Resource\Stock\Item | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Event\Manager | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    protected function setUp()
    {
        $this->resource = $this->getMock(
            'Magento\CatalogInventory\Model\Resource\Stock\Item',
            [],
            [],
            '',
            false
        );
        $this->eventManager = $this->getMock(
            'Magento\Framework\Event\Manager',
            ['dispatch'],
            [],
            '',
            false
        );
        $context = $this->getMock(
            '\Magento\Framework\Model\Context',
            ['getEventDispatcher'],
            [],
            '',
            false
        );
        $context->expects($this->any())
            ->method('getEventDispatcher')
            ->will($this->returnValue($this->eventManager));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->item = $objectManager->getObject(
            'Magento\CatalogInventory\Model\Stock\Item',
            [
                'resource' => $this->resource,
                'context' => $context
            ]
        );
    }

    protected function tearDown()
    {
        $this->item = null;
    }

    public function testSave()
    {
        $this->item->setData('key', 'value');

        $this->eventManager->expects($this->at(0))
            ->method('dispatch')
            ->with('model_save_before', ['object' => $this->item]);
        $this->eventManager->expects($this->at(1))
            ->method('dispatch')
            ->with('cataloginventory_stock_item_save_before', ['data_object' => $this->item, 'item' => $this->item]);


        $this->resource->expects($this->once())
            ->method('addCommitCallback')
            ->will($this->returnValue($this->resource));

        $this->assertEquals($this->item, $this->item->save());
    }
}
