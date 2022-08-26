<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model\ResourceModel\ImsWebapi;

use Magento\AdminAdobeIms\Model\ResourceModel\ImsWebapi as ImsWebapiResource;
use Magento\AdminAdobeIms\Model\ImsWebapi as ImsWebapiModel;
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
        $this->_init(ImsWebapiModel::class, ImsWebapiResource::class);
    }
}
