<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Service\V1\DownloadableLink;

interface ReadServiceInterface
{
    /**
     * List of samples for downloadable product
     *
     * @param string $productSku
     * @return \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableSampleInfo[]
     */
    public function getSamples($productSku);

    /**
     * List of links with associated samples
     *
     * @param string $productSku
     * @return \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableLinkInfo[]
     */
    public function getLinks($productSku);
}
