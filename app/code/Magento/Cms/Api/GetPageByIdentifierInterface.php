<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Cms\Api;

/**
 * Command to load the page data by specified identifier
 * @api
 * @since 103.0.0
 */
interface GetPageByIdentifierInterface
{
    /**
     * Load page data by given page identifier.
     *
     * @param string $identifier
     * @param int $storeId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return \Magento\Cms\Api\Data\PageInterface
     * @since 103.0.0
     */
    public function execute(string $identifier, int $storeId) : \Magento\Cms\Api\Data\PageInterface;
}
