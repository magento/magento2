<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model\ResourceModel\ImsToken;

use Magento\AdminAdobeIms\Model\ResourceModel\ImsToken as ImsTokenResource;
use Magento\AdminAdobeIms\Model\ImsToken as ImsTokenModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Represent the ims token collection
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init(ImsTokenModel::class, ImsTokenResource::class);
    }
}
