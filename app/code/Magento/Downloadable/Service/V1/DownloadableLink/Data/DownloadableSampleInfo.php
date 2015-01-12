<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Service\V1\DownloadableLink\Data;

use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * @codeCoverageIgnore
 */
class DownloadableSampleInfo extends AbstractExtensibleObject
{
    const ID = 'id';

    const TITLE = 'title';

    const SORT_ORDER = 'sort_order';

    const SAMPLE_RESOURCE = 'sample_resource';

    /**
     * Product sample id
     *
     * @return int|null Sample(or link) id
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Sample title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_get(self::TITLE);
    }

    /**
     * File or URL of sample
     *
     * @return \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableResourceInfo
     */
    public function getSampleResource()
    {
        return $this->_get(self::SAMPLE_RESOURCE);
    }

    /**
     * Sort order index for sample
     *
     * @return int
     */
    public function getSortOrder()
    {
        return (int)$this->_get(self::SORT_ORDER);
    }
}
