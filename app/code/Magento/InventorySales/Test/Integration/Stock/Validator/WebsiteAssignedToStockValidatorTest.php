<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventoryApi\Model\StockValidatorInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Provides tests for stock validator.
 */
class WebsiteAssignedToStockValidatorTest extends TestCase
{
    /**
     * @var StockValidatorInterface
     */
    private $stockValidator;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->stockValidator = Bootstrap::getObjectManager()->get(StockValidatorInterface::class);
        $this->stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
    }

    /**
     * Check stock validation with and without assigned sales channels.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testValidateSalesChannels() :void
    {
        $stock = $this->stockRepository->get(10);
        $validateErrors = $this->stockValidator->validate($stock)->getErrors();
        $this->assertEquals(0, count($validateErrors));
        /** @var /Magento/InventoryApi/Api/Data/StockExtension $extensionAttributes */
        $extensionAttributes = $stock->getExtensionAttributes();
        $extensionAttributes->setSalesChannels(null);
        $validateErrors = $this->stockValidator->validate($stock)->getErrors();
        $this->assertEquals(1, count($validateErrors));
        /** @var /Magento/Framework/Phrase $error */
        $error = array_shift($validateErrors);
        $this->assertEquals('Website "eu_website" should be linked to stock.', $error->render());
    }
}
