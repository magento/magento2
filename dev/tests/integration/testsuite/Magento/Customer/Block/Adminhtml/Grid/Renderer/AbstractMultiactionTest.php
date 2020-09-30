<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Adminhtml\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Extended;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Base class for testing \Magento\Customer\Block\Adminhtml\Grid\Renderer\Multiaction block
 */
abstract class AbstractMultiactionTest extends TestCase
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var Extended */
    protected $blockColumn;

    /** @var Multiaction */
    protected $blockMultiaction;

    /** @var LayoutInterface */
    private $layout;

    /** @var CollectionFactory */
    private $quoteItemCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->blockColumn = $this->layout->createBlock(Extended::class);
        $this->blockColumn->setData([
            'header' => 'Action',
            'index' => 'item_id',
            'renderer' => Multiaction::class,
            'filter' => false,
            'sortable' => false,
        ]);
        $this->blockMultiaction = $this->layout->createBlock(Multiaction::class);
        $this->quoteItemCollectionFactory = $this->objectManager->get(CollectionFactory::class);
    }

    /**
     * Check multiaction block rendering
     *
     * @return void
     */
    protected function processRender(): void
    {
        $itemsCollection = $this->quoteItemCollectionFactory->create();
        /** @var Item $quoteItem */
        $quoteItem = $itemsCollection->getFirstItem();
        $this->assertNotEmpty($quoteItem->getId());
        $actions = [
            [
                'caption' => 'configure',
                'url' => 'url_configureItem',
                'process' => 'configurable',
                'control_object' => 'cartControl',
            ],
            [
                'caption' => 'delete',
                'url' => 'url_removeItem',
                'onclick' => 'return cartControl.removeItem($item_id);'
            ],
        ];
        $this->blockColumn->addData(['actions' => $actions]);
        $this->blockMultiaction->setColumn($this->blockColumn);
        $html = $this->blockMultiaction->render($quoteItem);

        foreach ($actions as $action) {
            $this->assertUrl((int)$quoteItem->getId(), $action, $html);
        }
    }

    /**
     * Check that the link in the block is correct
     *
     * @param int $quoteItemId
     * @param array $action
     * @param string $html
     * @return void
     */
    private function assertUrl(int $quoteItemId, array $action, string $html): void
    {
        $jsFunction = str_replace('url_', '', $action['url']);
        $configureXPath = "//a[text()='{$action['caption']}' and @href='{$action['url']}']";
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath($configureXPath, $html),
            sprintf('Expected %s link is incorrect or missing', $action['caption'])
        );
        $this->assertStringContainsString("return cartControl.$jsFunction($quoteItemId)", $html);
    }
}
