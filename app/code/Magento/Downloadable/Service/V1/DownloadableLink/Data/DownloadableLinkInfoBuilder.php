<?php
/**
 * Downloadable Link Info Builder
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Downloadable\Service\V1\DownloadableLink\Data;

use Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder;

/**
 * @codeCoverageIgnore
 */
class DownloadableLinkInfoBuilder extends AbstractExtensibleObjectBuilder
{
    /**
     * @param int|null $value
     * @return $this
     */
    public function setId($value)
    {
        return $this->_set(DownloadableLinkInfo::ID, $value);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setTitle($value)
    {
        return $this->_set(DownloadableLinkInfo::TITLE, $value);
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setSortOrder($value)
    {
        return $this->_set(DownloadableLinkInfo::SORT_ORDER, $value);
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setShareable($value)
    {
        return $this->_set(DownloadableLinkInfo::SHAREABLE, $value);
    }

    /**
     * Link price
     *
     * @param float|null $value
     * @return $this
     */
    public function setPrice($value = 0.0)
    {
        return $this->_set(DownloadableLinkInfo::PRICE, $value);
    }

    /**
     * Number of downloads per user
     *
     * @param int|null $value
     * @return $this
     */
    public function setNumberOfDownloads($value = null)
    {
        return $this->_set(DownloadableLinkInfo::NUMBER_OF_DOWNLOADS, $value);
    }

    /**
     * Sample data object
     *
     * @param \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableResourceInfo|null $value
     * @return $this
     */
    public function setSampleResource($value = null)
    {
        return $this->_set(DownloadableLinkInfo::SAMPLE_RESOURCE, $value);
    }

    /**
     * Link data object
     *
     * @param \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableResourceInfo $value
     * @return $this
     */
    public function setLinkResource($value)
    {
        return $this->_set(DownloadableLinkInfo::LINK_RESOURCE, $value);
    }
}
