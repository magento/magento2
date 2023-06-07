<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Indexer\Stock\Action;

use Magento\CatalogInventory\Model\Indexer\Stock\Action\Row;
use Magento\CatalogInventory\Model\Indexer\Stock\Action\Rows;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class RowTest extends TestCase
{
    /**
     * @var Rows
     */
    protected $_model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject(Row::class);
    }

    public function testEmptyId()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('We can\'t rebuild the index for an undefined product.');
        $this->_model->execute(null);
    }
}
