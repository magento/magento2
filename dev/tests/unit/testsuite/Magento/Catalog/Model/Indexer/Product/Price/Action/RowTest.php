<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price\Action;

use Magento\TestFramework\Helper\ObjectManager;

class RowTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Action\Rows
     */
    protected $_model;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject('Magento\Catalog\Model\Indexer\Product\Price\Action\Row');
    }

    /**
     * @expectedException \Magento\Catalog\Exception
     * @expectedExceptionMessage Could not rebuild index for undefined product
     */
    public function testEmptyId()
    {
        $this->_model->execute(null);
    }
}
