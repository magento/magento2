<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Cms\Api;

/**
 * Command to load the block data by specified identifier
 * @api
 * @since 103.0.0
 */
interface GetBlockByIdentifierInterface
{
    /**
     * Load block data by given block identifier.
     *
     * @param string $identifier
     * @param int $storeId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return \Magento\Cms\Api\Data\BlockInterface
     * @since 103.0.0
     */
    public function execute(string $identifier, int $storeId) : \Magento\Cms\Api\Data\BlockInterface;
}
