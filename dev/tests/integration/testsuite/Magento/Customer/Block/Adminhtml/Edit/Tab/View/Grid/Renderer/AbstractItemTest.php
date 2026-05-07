<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Adminhtml\Edit\Tab\View\Grid\Renderer;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Helper\Product\Configuration;
use Magento\Customer\Block\Adminhtml\Edit\Tab\View\Grid\Renderer\Item as RendererItem;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Base class for testing \Magento\Customer\Block\Adminhtml\Edit\Tab\View\Grid\Renderer\Item block
 */
abstract class AbstractItemTest extends TestCase
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var RendererItem */
    private $blockRendererItem;

    /** @var CollectionFactory */
    private $quoteItemCollectionFactory;

    /** @var Configuration */
    private $productConfiguration;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->blockRendererItem = $this->objectManager->get(LayoutInterface::class)->createBlock(RendererItem::class);
        $this->quoteItemCollectionFactory = $this->objectManager->get(CollectionFactory::class);
        $this->productConfiguration = $this->objectManager->get(Configuration::class);
    }

    /**
     * Check item block rendering
     *
     * @return void
     */
    protected function processRender(): void
    {
        $itemsCollection = $this->quoteItemCollectionFactory->create();
        /** @var Item $quoteItem */
        $quoteItem = $itemsCollection->getFirstItem();
        $this->assertNotEmpty($quoteItem->getId());
        $this->blockRendererItem->setProductHelpers([]);
        $html = $this->blockRendererItem->render($quoteItem);

        $this->assertRendererItemValue($quoteItem, $html);
    }

    /**
     * Check that the product name and options are in the block.
     *
     * @param Item $quoteItem
     * @param string $html
     * @return void
     */
    private function assertRendererItemValue(Item $quoteItem, string $html): void
    {
        $optionsXPath = $this->getOptionsValueXPath($quoteItem);
        $productName = $quoteItem->getProduct()->getName();

        $productNameXPath = count($optionsXPath) === 0 ? "/descendant::*[contains(text(), '$productName')]"
            : "//div[contains(@class, 'product-title') and contains(text(), '$productName')]";

        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath($productNameXPath, $html),
            'The block\'s rendered value does not contain expected product name.'
        );
        foreach ($optionsXPath as $option) {
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath($option['xpath'], $html),
                sprintf('The block\'s rendered value does not contain expected option. Option: %s', $option['label'])
            );
        }
    }

    /**
     * Get item options and their xpath expression
     *
     * @param Item $quoteItem
     * @return array
     */
    private function getOptionsValueXPath(Item $quoteItem): array
    {
        $options = $this->productConfiguration->getOptions($quoteItem);
        foreach ($options as $key => $option) {
            $options[$key]['xpath'] = "//dl[contains(@class, 'item-options')]"
                . "/dt[contains(text(), '{$option['label']}')]"
                . "/following-sibling::dd[1]";

            if (isset($option['option_type'])
                && $option['option_type'] == ProductCustomOptionInterface::OPTION_GROUP_FILE) {
                $value = explode(" ", $option['print_value']);
                $options[$key]['xpath'] .= "[contains(text(), '{$value[0]}')]";
            } else {
                $options[$key]['xpath'] .= "[contains(text(), '{$option['value']}')]";
            }
        }

        return $options;
    }
}
