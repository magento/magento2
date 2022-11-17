<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Represent the ims token resource model
 */
class ImsWebapi extends AbstractDb
{
    private const ADMIN_ADOBE_IMS_WEBAPI = 'admin_adobe_ims_webapi';
    private const ENTITY_ID = 'id';

    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init(self::ADMIN_ADOBE_IMS_WEBAPI, self::ENTITY_ID);
    }
}
