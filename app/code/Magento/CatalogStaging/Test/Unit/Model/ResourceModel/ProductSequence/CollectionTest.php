<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace  Magento\CatalogStaging\Test\Unit\Model\Plugin\ResourceModel\ProductSequence;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DeleteSequenceObserverTest extends \PHPUnit_Framework_TestCase
{
    public function testDeleteSequence()
    {
        $objectManager = new ObjectManager($this);
        $metadataPoolMock = $this->getMock('Magento\Framework\Model\Entity\MetadataPool', [], [], '', false);
        $resourceMock = $this->getMock('Magento\Framework\App\ResourceConnection', [], [], '', false);
        $sequenceRegistryMock = $this->getMock('Magento\Framework\Model\Entity\SequenceRegistry', [], [], '', false);
        $metadataMock = $this->getMock('Magento\Framework\Model\Entity\EntityMetadata', [], [], '', false);
        $metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->willReturn($metadataMock);
        $connectionMock = $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface', [], [], '', false);
        $sequenceRegistryMock->expects($this->once())
            ->method('retrieve')
            ->willReturn(['sequenceTable' => 'sequence_table']);
        $metadataMock->expects($this->once())
            ->method('getEntityConnection')
            ->willReturn($connectionMock);
        /** @var \Magento\CatalogStaging\Model\ResourceModel\ProductSequence\Collection $model */
        $model = $objectManager->getObject(
            'Magento\CatalogStaging\Model\ResourceModel\ProductSequence\Collection',
            [
                'metadataPool' => $metadataPoolMock,
                'resource' => $resourceMock,
                'sequenceRegistry' => $sequenceRegistryMock
            ]
        );
        $resourceMock->expects($this->once())
            ->method('getTableName')
            ->with('sequence_table')
            ->willReturn('sequence_table');
        $ids = [1, 2, 3];
        $connectionMock->expects($this->once())
            ->method('delete')
            ->with('sequence_table', ['sequence_value IN (?)' => $ids]);
        $model->deleteSequence($ids);
    }
}
