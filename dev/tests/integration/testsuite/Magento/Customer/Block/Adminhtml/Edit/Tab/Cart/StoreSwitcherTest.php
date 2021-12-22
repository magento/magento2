<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Adminhtml\Edit\Tab\Cart;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Class checks store switcher appearance in the customer shopping cart block.
 *
 * @see \Magento\Customer\Block\Adminhtml\Edit\Tab\Cart
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class StoreSwitcherTest extends TestCase
{
    private const WEBSITE_FILTER_XPATH = "//select[@name='website_id' and @id='website_filter']";

    private const WEBSITE_FILTER_OPTION_XPATH = "//select[@name='website_id' and @id='website_filter']/option";

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var PageFactory */
    private $pageFactory;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->pageFactory = $this->objectManager->get(PageFactory::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @return void
     */
    public function testStoreSwitcherDisplayed(): void
    {
        $html = $this->getBlockHtml('admin.customer.view.edit.cart');
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(self::WEBSITE_FILTER_XPATH, $html),
            'Website Filter was not found on the page'
        );
        $this->checkFilterOptions($html, [$this->storeManager->getWebsite('base')->getName()]);
    }

    /**
     * @magentoConfigFixture current_store general/single_store_mode/enabled 1
     *
     * @return void
     */
    public function testStoreSwitcherIsNotDisplayed(): void
    {
        $html = $this->getBlockHtml('admin.customer.view.edit.cart');
        $this->assertEmpty(Xpath::getElementsCountForXpath(self::WEBSITE_FILTER_XPATH, $html));
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     *
     * @return void
     */
    public function testStoreSwitcherMultiWebsite(): void
    {
        $expectedWebsites = [
            $this->storeManager->getWebsite('base')->getName(),
            $this->storeManager->getWebsite('test')->getName(),
        ];
        $html = $this->getBlockHtml('admin.customer.view.edit.cart');
        $this->assertEquals(1, Xpath::getElementsCountForXpath(self::WEBSITE_FILTER_XPATH, $html));
        $this->checkFilterOptions($html, $expectedWebsites);
    }

    /**
     * Check store switcher appearance
     *
     * @param string $html
     * @param array $expectedOptions
     * @return void
     */
    private function checkFilterOptions(string $html, array $expectedOptions): void
    {
        $this->assertEquals(
            count($expectedOptions),
            Xpath::getElementsCountForXpath(self::WEBSITE_FILTER_OPTION_XPATH, $html),
            'Website filter options count does not match expected value'
        );
        $optionPath = self::WEBSITE_FILTER_OPTION_XPATH . "[contains(text(), '%s')]";
        foreach ($expectedOptions as $option) {
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(sprintf($optionPath, $option), $html),
                sprintf('Option for %s website was not found in filter options list', $option)
            );
        }
    }

    /**
     * Get block html
     *
     * @param string $alias
     * @return string
     */
    private function getBlockHtml(string $alias): string
    {
        $page = $this->preparePage();
        $block = $page->getLayout()->getBlock($alias);
        $this->assertNotFalse($block);

        return $block->toHtml();
    }

    /**
     * Prepare page layout
     *
     * @return Page
     */
    private function preparePage(): Page
    {
        $page = $this->pageFactory->create();
        $page->addHandle(['default', 'customer_index_cart']);
        $page->getLayout()->generateXml();

        return $page;
    }
}
