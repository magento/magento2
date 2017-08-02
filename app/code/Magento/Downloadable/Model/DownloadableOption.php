<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model;

use Magento\Downloadable\Api\Data\DownloadableOptionInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * @codeCoverageIgnore
 * @api
 * @since 2.0.0
 */
class DownloadableOption extends AbstractExtensibleModel implements DownloadableOptionInterface
{
    /**
     * Returns the list of downloadable links
     *
     * @return int[]
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setDownloadableLinks($downloadableLinks)
    {
        return $this->setData(self::DOWNLOADABLE_LINKS, $downloadableLinks);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Downloadable\Api\Data\DownloadableOptionExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Downloadable\Api\Data\DownloadableOptionExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Downloadable\Api\Data\DownloadableOptionExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
