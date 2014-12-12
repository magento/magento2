<?php
/**
 * @category    Magento
 * @package     Magento_CatalogInventory
 * @subpackage  unit_tests
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\CatalogInventory\Model\Indexer\Stock\Action;

use Magento\TestFramework\Helper\ObjectManager;

class RowsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Action\Rows
     */
    protected $_model;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject('Magento\CatalogInventory\Model\Indexer\Stock\Action\Rows');
    }

    /**
     * @expectedException \Magento\CatalogInventory\Exception
     * @expectedExceptionMessage Could not rebuild index for empty products array
     */
    public function testEmptyIds()
    {
        $this->_model->execute(null);
    }
}
