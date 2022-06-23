<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeIms\Model\ResourceModel\UserProfile;

use Magento\AdobeIms\Model\ResourceModel\UserProfile as UserProfileResource;
use Magento\AdobeIms\Model\UserProfile as UserProfileModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Represent the user profile collection
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init(UserProfileModel::class, UserProfileResource::class);
    }
}
