<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Api\Data;

/**
 * Downloadable Option
 * @api
 * @since 2.0.0
 */
interface DownloadableOptionInterface
{
    const DOWNLOADABLE_LINKS = 'downloadable_links';

    /**
     * Returns the list of downloadable links
     *
     * @return int[]
     * @since 2.0.0
     */
    public function getDownloadableLinks();

    /**
     * Sets the list of downloadable links
     *
     * @param int[] $downloadableLinks
     * @return $this
     * @since 2.0.0
     */
    public function setDownloadableLinks($downloadableLinks);
}
