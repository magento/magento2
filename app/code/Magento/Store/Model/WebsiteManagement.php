<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Store\Api\WebsiteManagementInterface;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;

/**
 * @api
 * @since 2.0.0
 */
class WebsiteManagement implements WebsiteManagementInterface
{
    /**
     * @var CollectionFactory
     * @since 2.0.0
     */
    protected $websitesFactory;

    /**
     * @param CollectionFactory $websitesFactory
     * @since 2.0.0
     */
    public function __construct(CollectionFactory $websitesFactory)
    {
        $this->websitesFactory = $websitesFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCount()
    {
        $websites = $this->websitesFactory->create();
        /** @var \Magento\Store\Model\ResourceModel\Website\Collection $websites */
        return $websites->getSize();
    }
}
