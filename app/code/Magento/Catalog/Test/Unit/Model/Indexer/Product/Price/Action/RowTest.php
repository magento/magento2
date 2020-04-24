<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Price\Action;

use Magento\Catalog\Model\Indexer\Product\Price\Action\Row;
use Magento\Catalog\Model\Indexer\Product\Price\Action\Rows;
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
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('We can\'t rebuild the index for an undefined product.');
        $this->_model->execute(null);
    }
}
