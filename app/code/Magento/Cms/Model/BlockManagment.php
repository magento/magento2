<?php

namespace Magento\Cms\Model;

use Magento\Cms\Api\BlockManagementInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @api
 */

class BlockManagment implements BlockManagementInterface
{
    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $blockFactory;

    /**
     * @var ResourceModel\Block
     */
    protected $blockResource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * BlockManagment constructor.
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param ResourceModel\Block $blockResource
     */
    public function __construct(
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Cms\Model\ResourceModel\Block $blockResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->blockFactory = $blockFactory;
        $this->blockResource = $blockResource;
        $this->storeManager = $storeManager;
    }

    /**
     * Load block data by given block identifier.
     *
     * @param string $identifier
     * @param int|null $storeId
     * @return \Magento\Cms\Api\Data\BlockInterface
     * @throws NoSuchEntityException
     */
    public function getByIdentifier($identifier, $storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $block = $this->blockFactory->create();
        $block->setStoreId($storeId);
        $this->blockResource->load($block, $identifier, BlockInterface::IDENTIFIER);

        if (!$block->getId()) {
            throw new NoSuchEntityException(__('CMS Block with identifier "%1" does not exist.', $identifier));
        }

        return $block;
    }
}