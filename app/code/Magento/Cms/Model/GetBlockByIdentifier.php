<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Model;

use Magento\Cms\Api\GetBlockByIdentifierInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GetBlockByIdentifier
 */
class GetBlockByIdentifier implements GetBlockByIdentifierInterface
{
    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    private $blockFactory;

    /**
     * @var ResourceModel\Block
     */
    private $blockResource;

    /**
     * @param BlockFactory $blockFactory
     * @param ResourceModel\Block $blockResource
     */
    public function __construct(
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Cms\Model\ResourceModel\Block $blockResource
    ) {
        $this->blockFactory = $blockFactory;
        $this->blockResource = $blockResource;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $identifier, int $storeId) : BlockInterface
    {
        $block = $this->blockFactory->create();
        $block->setStoreId($storeId);
        $this->blockResource->load($block, $identifier, BlockInterface::IDENTIFIER);

        if (!$block->getId()) {
            throw new NoSuchEntityException(__('The CMS block with the "%1" ID doesn\'t exist.', $identifier));
        }

        return $block;
    }
}
