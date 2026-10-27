<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Downloadable\Api\Data;

/**
 * Downloadable Option
 * @api
 * @since 100.0.2
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
