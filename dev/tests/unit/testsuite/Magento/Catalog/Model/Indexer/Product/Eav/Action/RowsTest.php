<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Indexer\Product\Eav\Action;

use Magento\TestFramework\Helper\ObjectManager;

class RowsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Action\Rows
     */
    protected $_model;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject('Magento\Catalog\Model\Indexer\Product\Eav\Action\Rows');
    }

    /**
     * @expectedException \Magento\Catalog\Exception
     * @expectedExceptionMessage Bad value was supplied.
     */
    public function testEmptyIds()
    {
        $this->_model->execute(null);
    }
}
