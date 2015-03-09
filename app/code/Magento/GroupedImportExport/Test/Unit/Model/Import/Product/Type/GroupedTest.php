<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedImportExport\Test\Unit\Model\Import\Product\Type;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use \Magento\GroupedImportExport;

class GroupedTest extends \PHPUnit_Framework_TestCase
{
    /** @var GroupedImportExport\Model\Import\Product\Type\Grouped */
    protected $grouped;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $setCollectionFactory;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $setCollection;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attrCollectionFactory;

    /**
     * @var []
     */
    protected $params;

    /**
     * @var GroupedImportExport\Model\Import\Product\Type\Grouped\Links|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $links;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityModel;

    protected function setUp()
    {
        $this->setCollectionFactory = $this->getMock(
            'Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->setCollection = $this->getMock(
            'Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection',
            ['setEntityTypeFilter'],
            [],
            '',
            false
        );
        $this->setCollectionFactory->expects($this->any())->method('create')->will(
            $this->returnValue($this->setCollection)
        );
        $this->setCollection->expects($this->any())
            ->method('setEntityTypeFilter')
            ->will($this->returnValue([]));

        $this->attrCollectionFactory = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory',
            [],
            [],
            '',
            false
        );
        $this->entityModel = $this->getMock(
            'Magento\CatalogImportExport\Model\Import\Product',
            ['getNewSku', 'getNextBunch', 'isRowAllowedToImport', 'getRowScope'],
            [],
            '',
            false
        );
        $this->params = [
            0 => $this->entityModel,
            1 => 'grouped'
        ];
        $this->links = $this->getMock(
            'Magento\GroupedImportExport\Model\Import\Product\Type\Grouped\Links',
            [],
            [],
            '',
            false
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->grouped = $this->objectManagerHelper->getObject(
            'Magento\GroupedImportExport\Model\Import\Product\Type\Grouped',
            [
                'attrSetColFac' => $this->setCollectionFactory,
                'prodAttrColFac' => $this->attrCollectionFactory,
                'params' => $this->params,
                'links' => $this->links
            ]
        );
    }

    public function testSaveData()
    {
        $associatedSku = 'sku_assoc';
        $productSku = 'productSku';
        $this->entityModel->expects($this->once())->method('getNewSku')->will($this->returnValue([
            $associatedSku => ['entity_id' => 1],
            $productSku => ['entity_id' => 2]
        ]));
        $attributes = ['position' => ['id' => 0], 'qty' => ['id' => 0]];
        $this->links->expects($this->once())->method('getAttributes')->will($this->returnValue($attributes));

        $bunch = [[
            '_associated_sku' => $associatedSku,
            'sku' => $productSku,
            '_type' => 'grouped',
            '_associated_default_qty' => 4,
            '_associated_position' => 6
        ]];
        $this->entityModel->expects($this->at(0))->method('getNextBunch')->will($this->returnValue($bunch));
        $this->entityModel->expects($this->at(1))->method('getNextBunch')->will($this->returnValue($bunch));
        $this->entityModel->expects($this->any())->method('isRowAllowedToImport')->will($this->returnValue(true));
        $this->entityModel->expects($this->any())->method('getRowScope')->will($this->returnValue(
            \Magento\CatalogImportExport\Model\Import\Product::SCOPE_DEFAULT
        ));

        $this->links->expects($this->once())->method("saveLinksData");
        $this->grouped->saveData();
    }
}
