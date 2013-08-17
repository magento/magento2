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
 * @package     Mage_ImportExport
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_ImportExport_Model_Resource_CollectionByPagesIterator
 */
class Mage_ImportExport_Model_Resource_CollectionByPagesIteratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_ImportExport_Model_Resource_CollectionByPagesIterator
     */
    protected $_resourceModel;

    protected function setUp()
    {
        $this->_resourceModel = new Mage_ImportExport_Model_Resource_CollectionByPagesIterator();
    }

    protected function tearDown()
    {
        unset($this->_resourceModel);
    }

    /**
     * @covers Mage_ImportExport_Model_Resource_CollectionByPagesIterator::iterate
     */
    public function testIterate()
    {
        $pageSize  = 2;
        $pageCount = 3;

        /** @var $callbackMock PHPUnit_Framework_MockObject_MockObject */
        $callbackMock = $this->getMock('stdClass', array('callback'));

        $fetchStrategy = $this->getMockForAbstractClass('Varien_Data_Collection_Db_FetchStrategyInterface');

        $select = $this->getMock('Zend_Db_Select', array(), array(), '', false);

        /** @var $collectionMock Varien_Data_Collection_Db|PHPUnit_Framework_MockObject_MockObject */
        $collectionMock = $this->getMock('Varien_Data_Collection_Db',
            array('clear', 'setPageSize', 'setCurPage', 'count', 'getLastPageNumber', 'getSelect'),
            array($fetchStrategy)
        );

        $collectionMock->expects($this->any())
            ->method('getSelect')
            ->will($this->returnValue($select));

        $collectionMock->expects($this->exactly($pageCount + 1))
            ->method('clear')
            ->will($this->returnSelf());

        $collectionMock->expects($this->exactly($pageCount))
            ->method('setPageSize')
            ->will($this->returnSelf());

        $collectionMock->expects($this->exactly($pageCount))
            ->method('setCurPage')
            ->will($this->returnSelf());

        $collectionMock->expects($this->exactly($pageCount))
            ->method('count')
            ->will($this->returnValue($pageSize));

        $collectionMock->expects($this->exactly($pageCount))
            ->method('getLastPageNumber')
            ->will($this->returnValue($pageCount));

        for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
            for ($rowNumber = 1; $rowNumber <= $pageSize; $rowNumber++) {
                $itemId = ($pageNumber - 1)*$pageSize + $rowNumber;
                $item = new Varien_Object(array('id' => $itemId));
                $collectionMock->addItem($item);

                $callbackMock->expects($this->at($itemId - 1))
                    ->method('callback')
                    ->with($item);
            }
        }

        $this->_resourceModel->iterate($collectionMock, $pageSize, array(array($callbackMock, 'callback')));
    }
}
