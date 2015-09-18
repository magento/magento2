<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model;

use Magento\Downloadable\Api\Data\DownloadableOptionInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class DownloadableOption extends AbstractExtensibleModel implements DownloadableOptionInterface
{
    /**
     * Returns the list of downloadable links
     *
     * @return int[]
     */
    public function getDownloadableLinks()
    {
        return $this->getData(self::DOWNLOADABLE_LINKS);
    }

    /**
     * Sets the list of downloadable links
     *
     * @param int[] $downloadableLinks
     * @return $this
     */
    public function setDownloadableLinks($downloadableLinks)
    {
        return $this->setData(self::DOWNLOADABLE_LINKS, $downloadableLinks);
    }
}
