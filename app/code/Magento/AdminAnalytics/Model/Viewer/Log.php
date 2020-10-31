<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAnalytics\Model\Viewer;

use Magento\Framework\DataObject;

/**
 * Admin Analytics log resource
 */
class Log extends DataObject
{
    /**
     * Get log id
     *
     * @return int
     */
    public function getId() : ?int
    {
        return $this->getData('id');
    }

    /**
     * Get last viewed product version
     *
     * @return string
     */
    public function getLastViewVersion() : ?string
    {
        return $this->getData('last_viewed_in_version');
    }
}
