<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api;

use Magento\Downloadable\Api\Data\LinkContentInterface;

interface LinkRepositoryInterface
{
    /**
     * List of samples for downloadable product
     *
     * @param string $productSku
     * @return \Magento\Downloadable\Api\Data\SampleInterface[]
     */
    public function getSamples($productSku);

    /**
     * List of links with associated samples
     *
     * @param string $productSku
     * @return \Magento\Downloadable\Api\Data\LinkInterface[]
     */
    public function getLinks($productSku);

    /**
     * Update downloadable link of the given product (link type and its resources cannot be changed)
     *
     * @param string $productSku
     * @param \Magento\Downloadable\Api\Data\LinkContentInterface $linkContent
     * @param int|null $linkId
     * @param bool $isGlobalScopeContent
     * @return bool
     */
    public function save($productSku, LinkContentInterface $linkContent, $linkId = null, $isGlobalScopeContent = false);

    /**
     * Delete downloadable link
     *
     * @param int $linkId
     * @return bool
     */
    public function delete($linkId);
}
