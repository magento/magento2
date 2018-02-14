<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryCatalog\Model\IsSingleStockModeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class IsSingleStockModeTest extends TestCase
{
    /**
     * @var IsSingleStockModeInterface
     */
    protected $isSingleStockMode;

    /**
     * @var SourceRepositoryInterface
     */
    protected $sourceRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->isSingleStockMode = Bootstrap::getObjectManager()->get(IsSingleStockModeInterface::class);
        $this->sourceRepository = Bootstrap::getObjectManager()->get(SourceRepositoryInterface::class);
    }

    public function testIsSingleStockModeOnCleanInstall()
    {
        self::assertTrue($this->isSingleStockMode->execute());
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source.php
     */
    public function testIsSingleStockOnTwoSourcesOneDisabled()
    {
        $sourceToDisable = $this->sourceRepository->get('source-code-1');
        $sourceToDisable->setEnabled(false);
        $this->sourceRepository->save($sourceToDisable);

        self::assertCount(2, $this->sourceRepository->getList()->getItems());
        self::assertTrue($this->isSingleStockMode->execute());
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     */
    public function testIsMultiStock()
    {
        self::assertTrue(count($this->sourceRepository->getList()->getItems()) > 1);
        self::assertFalse($this->isSingleStockMode->execute());
    }
}
