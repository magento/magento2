<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Store\Api\WebsiteManagementInterface;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;

/**
 * @api
 */
class WebsiteManagement implements WebsiteManagementInterface
{
    /**
     * @var CollectionFactory
     */
    protected $websitesFactory;

    /**
     * @param CollectionFactory $websitesFactory
     */
    public function __construct(CollectionFactory $websitesFactory)
    {
        $this->websitesFactory = $websitesFactory;
    }
    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        $websites = $this->websitesFactory->create();
        /** @var \Magento\Store\Model\ResourceModel\Website\Collection $websites */
        return $websites->getSize();
    }
}
