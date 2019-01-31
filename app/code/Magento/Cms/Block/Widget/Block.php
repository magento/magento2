<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Block\Widget;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Cms\Model\Block as CmsBlock;
use Magento\Widget\Block\BlockInterface;

/**
 * Cms Static Block Widget
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Block extends \Magento\Framework\View\Element\Template implements BlockInterface, IdentityInterface
{
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * Storage for used widgets
     *
     * @var array
     */
    protected static $_widgetUsageMap = [];

    /**
     * Block factory
     *
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var CmsBlock
     */
    private $block;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_filterProvider = $filterProvider;
        $this->_blockFactory = $blockFactory;
    }

    /**
     * Prepare block text and determine whether block output enabled or not.
     *
     * Prevent blocks recursion if needed.
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        $blockId = $this->getData('block_id');
        $blockHash = get_class($this) . $blockId;

        if (isset(self::$_widgetUsageMap[$blockHash])) {
            return $this;
        }
        self::$_widgetUsageMap[$blockHash] = true;

        $block = $this->getBlock();

        if ($block && $block->isActive()) {
            try {
                $storeId = $this->_storeManager->getStore()->getId();
                $this->setText(
                    $this->_filterProvider->getBlockFilter()->setStoreId($storeId)->filter($block->getContent())
                );
            } catch (NoSuchEntityException $e) {
            }
        }
        unset(self::$_widgetUsageMap[$blockHash]);
        return $this;
    }

    /**
     * Get identities of the Cms Block
     *
     * @return array
     */
    public function getIdentities()
    {
        $block = $this->getBlock();

        if ($block) {
            return $block->getIdentities();
        }

        return [];
    }

    /**
     * Get block
     *
     * @return CmsBlock|null
     */
    private function getBlock(): ?CmsBlock
    {
        if ($this->block) {
            return $this->block;
        }

        $blockId = $this->getData('block_id');

        if ($blockId) {
            try {
                $storeId = $this->_storeManager->getStore()->getId();
                /** @var \Magento\Cms\Model\Block $block */
                $block = $this->_blockFactory->create();
                $block->setStoreId($storeId)->load($blockId);
                $this->block = $block;

                return $block;
            } catch (NoSuchEntityException $e) {
            }
        }

        return null;
    }
}
