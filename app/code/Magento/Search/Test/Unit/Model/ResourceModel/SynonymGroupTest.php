<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Model\ResourceModel\SynonymGroup;
use PHPUnit\Framework\TestCase;

class SynonymGroupTest extends TestCase
{
    public function testGetByScope()
    {
        $context = $this->createMock(Context::class);
        $resources = $this->createMock(ResourceConnection::class);
        $connection = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            '',
            false
        );
        $select = $this->createMock(Select::class);

        $connection->expects($this->exactly(2))->method('quoteIdentifier')->willReturn('quoted');
        $connection->expects($this->once())->method('select')->willReturn($select);
        $context->expects($this->once())->method('getResources')->willReturn($resources);
        $resources->expects($this->any())->method('getConnection')->willReturn($connection);
        $select->expects($this->once())->method('from')->willReturn($select);
        $select->expects($this->exactly(2))->method('where')->with('quoted=?', 0)->willReturn($select);
        $connection->expects($this->once())->method('fetchAll')->with($select);

        $resourceModel = (new ObjectManager($this))
            ->getObject(SynonymGroup::class, ['context' => $context]);

        $resourceModel->getByScope(0, 0);
    }
}
