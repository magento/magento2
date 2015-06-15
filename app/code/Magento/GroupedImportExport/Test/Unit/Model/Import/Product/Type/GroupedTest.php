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
            ['create'],
            [],
            '',
            false
        );
        $this->entityModel = $this->getMock(
            'Magento\CatalogImportExport\Model\Import\Product',
            ['getNewSku', 'getOldSku', 'getNextBunch', 'isRowAllowedToImport', 'getRowScope'],
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

    /**
     * Test for method saveData()
     *
     * @param array $skus
     * @param array $bunch
     *
     * @dataProvider testSaveDataProvider
     */
    public function testSaveData($skus, $bunch)
    {
        $this->entityModel->expects($this->once())->method('getNewSku')->will($this->returnValue($skus['newSku']));
        $this->entityModel->expects($this->once())->method('getOldSku')->will($this->returnValue($skus['oldSku']));
        $attributes = ['position' => ['id' => 0], 'qty' => ['id' => 0]];
        $this->links->expects($this->once())->method('getAttributes')->will($this->returnValue($attributes));

        $this->entityModel->expects($this->at(2))->method('getNextBunch')->will($this->returnValue([$bunch]));
        $this->entityModel->expects($this->any())->method('isRowAllowedToImport')->will($this->returnValue(true));
        $this->entityModel->expects($this->any())->method('getRowScope')->will($this->returnValue(
            \Magento\CatalogImportExport\Model\Import\Product::SCOPE_DEFAULT
        ));

        $this->links->expects($this->once())->method('saveLinksData');
        $this->grouped->saveData();
    }

    /**
     * Data provider for saveData()
     *
     * @return array
     */
    public function testSaveDataProvider()
    {
        return [
            [
                'skus' => [
                    'newSku' => [
                        'sku_assoc1' => ['entity_id' => 1],
                        'productSku' => ['entity_id' => 3, 'attr_set_code' => 'Default', 'type_id' => 'grouped']
                    ],
                    'oldSku' => ['sku_assoc2' => ['entity_id' => 2]]
                ],
                'bunch' => [
                    'associated_skus' => 'sku_assoc1=1, sku_assoc2=2',
                    'sku' => 'productSku',
                    'product_type' => 'grouped'
                ]
            ],
            [
                'skus' => [
                    'newSku' => [
                        'productSku' => ['entity_id' => 1, 'attr_set_code' => 'Default', 'type_id' => 'grouped']
                    ],
                    'oldSku' => []
                ],
                'bunch' => [
                    'associated_skus' => '',
                    'sku' => 'productSku',
                    'product_type' => 'grouped'
                ]
            ],
            [
                'skus' => ['newSku' => [],'oldSku' => []],
                'bunch' => [
                    'associated_skus' => 'sku_assoc1=1, sku_assoc2=2',
                    'sku' => 'productSku',
                    'product_type' => 'grouped'
                ]
            ],
            [
                'skus' => [
                    'newSku' => [
                        'sku_assoc1' => ['entity_id' => 1],
                        'productSku' => ['entity_id' => 3, 'attr_set_code' => 'Default', 'type_id' => 'grouped']
                    ],
                    'oldSku' => []
                ],
                'bunch' => [
                    'associated_skus' => 'sku_assoc1=1',
                    'sku' => 'productSku',
                    'product_type' => 'simple'
                ]
            ]
        ];
    }

    /**
     * Test saveData() with store row scope
     */
    public function testSaveDataScopeStore()
    {
        $this->entityModel->expects($this->once())->method('getNewSku')->will($this->returnValue([
            'sku_assoc1' => ['entity_id' => 1],
            'productSku' => ['entity_id' => 2, 'attr_set_code' => 'Default', 'type_id' => 'grouped']
        ]));
        $this->entityModel->expects($this->once())->method('getOldSku')->will($this->returnValue([
            'sku_assoc2' => ['entity_id' => 3]
        ]));
        $attributes = ['position' => ['id' => 0], 'qty' => ['id' => 0]];
        $this->links->expects($this->once())->method('getAttributes')->will($this->returnValue($attributes));

        $bunch = [[
            'associated_skus' => 'sku_assoc1=1, sku_assoc2=2',
            'sku' => 'productSku',
            'product_type' => 'grouped'
        ]];
        $this->entityModel->expects($this->at(2))->method('getNextBunch')->will($this->returnValue($bunch));
        $this->entityModel->expects($this->any())->method('isRowAllowedToImport')->will($this->returnValue(true));
        $this->entityModel->expects($this->at(4))->method('getRowScope')->will($this->returnValue(
            \Magento\CatalogImportExport\Model\Import\Product::SCOPE_DEFAULT
        ));
        $this->entityModel->expects($this->at(5))->method('getRowScope')->will($this->returnValue(
            \Magento\CatalogImportExport\Model\Import\Product::SCOPE_STORE
        ));

        $this->links->expects($this->once())->method('saveLinksData');
        $this->grouped->saveData();
    }
}
