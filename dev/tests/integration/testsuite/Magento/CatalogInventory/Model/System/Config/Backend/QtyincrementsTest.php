<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Model\System\Config\Backend;

use Magento\CatalogInventory\Model\Configuration;
use Magento\Config\Model\Config\BackendFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks that the qty increments config backend model is working correctly
 *
 * @see \Magento\CatalogInventory\Model\System\Config\Backend\Qtyincrements
 *
 * @magentoAppArea adminhtml
 */
class QtyincrementsTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Qtyincrements */
    private $qtyIncrements;

    /** @var BackendFactory */
    private $backendFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->backendFactory = $this->objectManager->get(BackendFactory::class);
        $this->qtyIncrements = $this->backendFactory->create(Qtyincrements::class, [
            'data' => [
                'path' => Configuration::XML_PATH_QTY_INCREMENTS,
            ],
        ]);
    }

    /**
     * @return void
     */
    public function testAfterSaveWithDecimals(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(
            (string)__("Quantity increments can't use decimals. Enter a new increment and try again.")
        );
        $value = 10.5;
        $this->qtyIncrements->setValue((string)$value);
        $this->qtyIncrements->beforeSave();
    }
}
