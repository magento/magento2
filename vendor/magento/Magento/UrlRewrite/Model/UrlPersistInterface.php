<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\UrlRewrite\Model;

/**
 * Url Persist Interface
 */
interface UrlPersistInterface
{
    /**
     * Save new url rewrites and remove old if exist
     *
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[] $urls
     * @return void
     * @throws \Magento\UrlRewrite\Model\Storage\DuplicateEntryException
     */
    public function replace(array $urls);

    /**
     * Remove rewrites that contains some rewrites data
     *
     * @param array $data
     * @return void
     */
    public function deleteByData(array $data);
}
