<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
