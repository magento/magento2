<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api\Data;

/**
 * Downloadable Option
 * @api
 */
interface DownloadableOptionInterface
{
    const DOWNLOADABLE_LINKS = 'downloadable_links';

    /**
     * Returns the list of downloadable links
     *
     * @return int[]
     */
    public function getDownloadableLinks();

    /**
     * Sets the list of downloadable links
     *
     * @param int[] $downloadableLinks
     * @return $this
     */
    public function setDownloadableLinks($downloadableLinks);
}
