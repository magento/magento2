<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\GroupedImportExport\Model\Import\Product\Type;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;
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

    /** @var \SafeReflectionClass|\PHPUnit_Framework_MockObject_MockObject */
    protected $params;

    /**
     * @var GroupedImportExport\Model\Import\Product\Type\Grouped\DbHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dbHelper;

    /** @var  \Magento\CatalogImportExport\Model\Import\Product */
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
        $this->dbHelper = $this->getMock(
            'Magento\GroupedImportExport\Model\Import\Product\Type\Grouped\DbHelper',
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
                'dbHelper' => $this->dbHelper
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
        $attributes = [];
        $this->dbHelper->expects($this->once())->method('getAttributes')->will($this->returnValue($attributes));

        $bunch = [['_associated_sku' => $associatedSku, 'sku' => $productSku, '_type' => 'grouped']];
        $this->entityModel->expects($this->at(0))->method('getNextBunch')->will($this->returnValue($bunch));
        $this->entityModel->expects($this->at(1))->method('getNextBunch')->will($this->returnValue($bunch));
        $this->entityModel->expects($this->any())->method('isRowAllowedToImport')->will($this->returnValue(true));
        $this->entityModel->expects($this->any())->method('getRowScope')->will($this->returnValue(
            \Magento\CatalogImportExport\Model\Import\Product::SCOPE_DEFAULT
        ));

        $this->dbHelper->expects($this->once())->method("saveLinksData");
        $this->grouped->saveData();
    }
}
