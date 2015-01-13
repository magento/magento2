<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\Indexer\Product;

use Magento\TestFramework\Helper\ObjectManager;

class ProductRuleIndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\Indexer\IndexBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexBuilder;

    /**
     * @var \Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer
     */
    protected $indexer;

    protected function setUp()
    {
        $this->indexBuilder = $this->getMock('Magento\CatalogRule\Model\Indexer\IndexBuilder', [], [], '', false);

        $this->indexer = (new ObjectManager($this))->getObject(
            'Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer',
            [
                'indexBuilder' => $this->indexBuilder,
            ]
        );
    }
    /**
     * @param array $ids
     * @param array $idsForIndexer
     * @dataProvider dataProviderForExecuteList
     */
    public function testDoExecuteList($ids, $idsForIndexer)
    {
        $this->indexBuilder->expects($this->once())->method('reindexByIds')->with($idsForIndexer);

        $this->indexer->executeList($ids);
    }

    /**
     * @return array
     */
    public function dataProviderForExecuteList()
    {
        return [
            [
                [1, 2, 3, 2, 3],
                [1, 2, 3],
            ],
            [
                [1, 2, 3],
                [1, 2, 3],
            ],
        ];
    }

    public function testDoExecuteRow()
    {
        $id = 5;
        $this->indexBuilder->expects($this->once())->method('reindexById')->with($id);

        $this->indexer->executeRow($id);
    }
}
