<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Model\System\Config\Backend;

use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\CatalogInventory\Model\Stock;

/**
 * Minqty test.
 */
class MinqtyTest extends TestCase
{
    /**
     * @var Minqty
     */
    private $minQtyConfig;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->minQtyConfig = $objectManager->create(Minqty::class);
        $this->minQtyConfig->setPath('cataloginventory/item_options/min_qty');
    }

    /**
     * Tests beforeSave method.
     *
     * @param string $value
     * @param array $fieldSetData
     * @param string $expected
     * @return void
     *
     * @dataProvider minQtyConfigDataProvider
     */
    public function testBeforeSave(string $value, array $fieldSetData, string $expected): void
    {
        $this->minQtyConfig->setData('fieldset_data', $fieldSetData);
        $this->minQtyConfig->setValue($value);
        $this->minQtyConfig->beforeSave();
        $this->assertEquals($expected, $this->minQtyConfig->getValue());
    }

    /**
     * Minqty config data provider.
     *
     * @return array
     */
    public function minQtyConfigDataProvider(): array
    {
        return [
            'straight' => ['3', ['backorders' => Stock::BACKORDERS_NO], '3'],
            'straight2' => ['3.5', ['backorders' => Stock::BACKORDERS_NO], '3.5'],
            'negative_value_disabled_backorders' => ['-3', ['backorders' => Stock::BACKORDERS_NO], '0'],
            'negative_value_enabled_backorders' => ['-3', ['backorders' => Stock::BACKORDERS_YES_NOTIFY], '-3'],
            'negative_value_enabled_backorders2' => ['-3.05', ['backorders' => Stock::BACKORDERS_YES_NOTIFY], '-3.05'],
        ];
    }
}
