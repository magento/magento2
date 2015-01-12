<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\Indexer\Rule;

use Magento\TestFramework\Helper\ObjectManager;

class RuleProductIndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\IndexBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexBuilder;

    /**
     * @var \Magento\CatalogRule\Model\Indexer\Rule\RuleProductIndexer
     */
    protected $indexer;

    protected function setUp()
    {
        $this->indexBuilder = $this->getMock('Magento\CatalogRule\Model\Indexer\IndexBuilder', [], [], '', false);

        $this->indexer = (new ObjectManager($this))->getObject(
            'Magento\CatalogRule\Model\Indexer\Rule\RuleProductIndexer',
            [
                'indexBuilder' => $this->indexBuilder,
            ]
        );
    }

    public function testDoExecuteList()
    {
        $this->indexBuilder->expects($this->once())->method('reindexFull');

        $this->indexer->executeList([1, 2, 5]);
    }

    public function testDoExecuteRow()
    {
        $this->indexBuilder->expects($this->once())->method('reindexFull');

        $this->indexer->executeRow(5);
    }
}
