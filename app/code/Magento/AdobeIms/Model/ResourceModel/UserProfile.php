<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeIms\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Represent the user profile resource model
 */
class UserProfile extends AbstractDb
{
    private const ADOBE_USER_PROFILE = 'adobe_user_profile';
    private const ENTITY_ID = 'id';

    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init(self::ADOBE_USER_PROFILE, self::ENTITY_ID);
    }
}
