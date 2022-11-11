<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Unit\Model\ResourceModel;

use Codeception\PHPUnit\TestCase;
use Magento\Bundle\Model\ResourceModel\Selection as ResourceSelection;
use Magento\Bundle\Model\Selection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class to test Selection Resource Model
 */
class SelectionTest extends TestCase
{
    public function testSaveSelectionPrice()
    {
        $item = $this->getMockBuilder(Selection::class)
            ->disableOriginalConstructor()
            ->addMethods([
                'getSelectionId',
                'getWebsiteId',
                'getSelectionPriceType',
                'getSelectionPriceValue',
                'getParentProductId',
                'getDefaultPriceScope'])
            ->getMock();
        $values = [
            'selection_id' => 1,
            'website_id' => 1,
            'selection_price_type' => null,
            'selection_price_value' => null,
            'parent_product_id' => 1,
        ];
        $item->expects($this->once())->method('getDefaultPriceScope')->willReturn(false);
        $item->expects($this->once())->method('getSelectionId')->willReturn($values['selection_id']);
        $item->expects($this->once())->method('getWebsiteId')->willReturn($values['website_id']);
        $item->expects($this->once())->method('getSelectionPriceType')->willReturn($values['selection_price_type']);
        $item->expects($this->once())->method('getSelectionPriceValue')->willReturn($values['selection_price_value']);
        $item->expects($this->once())->method('getParentProductId')->willReturn($values['parent_product_id']);

        $selection = $this->getMockBuilder(ResourceSelection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConnection', 'getTable'])
            ->getMock();
        $selection->expects($this->any())
            ->method('getTable')
            ->with('catalog_product_bundle_selection_price')
            ->willReturn('catalog_product_bundle_selection_price');

        $connection = $this->createMock(AdapterInterface::class);
        $connection->expects($this->once())
            ->method('insertOnDuplicate')
            ->with(
                $selection->getTable('catalog_product_bundle_selection_price'),
                $this->callback(function ($insertValues) {
                    return $insertValues['selection_price_type'] === 0 && $insertValues['selection_price_value'] === 0;
                }),
                ['selection_price_type', 'selection_price_value']
            );

        $selection->expects($this->once())->method('getConnection')->willReturn($connection);
        $selection->saveSelectionPrice($item);
    }
}
