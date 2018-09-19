<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Eav\Action;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class RowTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Action\Row
     */
    protected $_model;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject(\Magento\Catalog\Model\Indexer\Product\Eav\Action\Row::class);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage We can't rebuild the index for an undefined product.
     */
    public function testEmptyId()
    {
        $this->_model->execute(null);
    }
}
