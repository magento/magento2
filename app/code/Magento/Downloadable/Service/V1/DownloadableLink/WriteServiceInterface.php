<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Service\V1\DownloadableLink;

use Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableLinkContent;

interface WriteServiceInterface
{
    /**
     * Add downloadable link to the given product
     *
     * @param string $productSku
     * @param \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableLinkContent $linkContent
     * @param bool $isGlobalScopeContent
     * @return int link ID
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function create($productSku, DownloadableLinkContent $linkContent, $isGlobalScopeContent = false);

    /**
     * Update downloadable link of the given product (link type and its resources cannot be changed)
     *
     * @param string $productSku
     * @param int $linkId
     * @param \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableLinkContent $linkContent
     * @param bool $isGlobalScopeContent
     * @return bool
     */
    public function update($productSku, $linkId, DownloadableLinkContent $linkContent, $isGlobalScopeContent = false);

    /**
     * Delete downloadable link
     *
     * @param int $linkId
     * @return bool
     */
    public function delete($linkId);
}
