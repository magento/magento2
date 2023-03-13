<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Store\Api\WebsiteManagementInterface;
use Magento\Store\Model\ResourceModel\Website\Collection as WebsiteCollection;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;

/**
 * @api
 * @since 100.0.2
 */
class WebsiteManagement implements WebsiteManagementInterface
{
    /**
     * @param CollectionFactory $websitesFactory
     */
    public function __construct(
        protected readonly CollectionFactory $websitesFactory
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        /** @var WebsiteCollection $websites */
        $websites = $this->websitesFactory->create();
        return $websites->getSize();
    }
}
