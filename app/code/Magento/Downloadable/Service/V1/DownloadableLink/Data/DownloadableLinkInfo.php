<?php
/**
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

use Magento\Framework\Service\Data\AbstractExtensibleObject;

/**
 * @codeCoverageIgnore
 */
class DownloadableLinkInfo extends AbstractExtensibleObject
{
    const ID = 'id';

    const TITLE = 'title';

    const SORT_ORDER = 'sort_order';

    const SHAREABLE = 'shareable';

    const PRICE = 'price';

    const NUMBER_OF_DOWNLOADS = 'number_of_downloads';

    const SAMPLE_RESOURCE = 'sample_resource';

    const LINK_RESOURCE = 'link_resource';

    /**
     * Product link id
     *
     * @return int|null Sample(or link) id
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Link title
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->_get(self::TITLE);
    }

    /**
     * Sort order index for link
     *
     * @return int
     */
    public function getSortOrder()
    {
        return (int)$this->_get(self::SORT_ORDER);
    }

    /**
     * Link shareable status
     * 0 -- No
     * 1 -- Yes
     * 2 -- Use config default value
     *
     * @return int
     */
    public function getShareable()
    {
        return (int)$this->_get(self::SHAREABLE);
    }

    /**
     * Link price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->_get(self::PRICE);
    }

    /**
     * Number of downloads per user
     * Null for unlimited downloads
     *
     * @return int|null
     */
    public function getNumberOfDownloads()
    {
        return $this->_get(self::NUMBER_OF_DOWNLOADS);
    }

    /**
     * File or URL of sample if any
     *
     * @return \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableResourceInfo|null
     */
    public function getSampleResource()
    {
        return $this->_get(self::SAMPLE_RESOURCE);
    }

    /**
     * File or URL of link
     *
     * @return \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableResourceInfo
     */
    public function getLinkResource()
    {
        return $this->_get(self::LINK_RESOURCE);
    }
}
