<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Model\ResourceModel;

class SynonymGroupTest extends \PHPUnit_Framework_TestCase
{
    public function testGetByScope()
    {
        $context = $this->getMock('Magento\Framework\Model\ResourceModel\Db\Context', [], [], '', false);
        $resources = $this->getMock('Magento\Framework\App\ResourceConnection', [], [], '', false);
        $connection = $this->getMockForAbstractClass('Magento\Framework\DB\Adapter\AdapterInterface', [], '', false);
        $select = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);


        $connection->expects($this->exactly(2))->method('quoteIdentifier')->willReturn('quoted');
        $connection->expects($this->once())->method('select')->willReturn($select);
        $context->expects($this->once())->method('getResources')->willReturn($resources);
        $resources->expects($this->any())->method('getConnection')->willReturn($connection);
        $select->expects($this->once())->method('from')->willReturn($select);
        $select->expects($this->exactly(2))->method('where')->with('quoted=?', 0)->willReturn($select);
        $connection->expects($this->once())->method('fetchAll')->with($select);

        $resourceModel = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject('Magento\Search\Model\ResourceModel\SynonymGroup', ['context' => $context]);

        $resourceModel->getByScope(0, 0);
    }
}
