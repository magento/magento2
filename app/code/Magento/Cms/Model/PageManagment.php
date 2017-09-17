<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageManagmentInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @api
 */

class PageManagment implements PageManagmentInterface
{
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $pageFactory;

    /**
     * @var ResourceModel\Page
     */
    protected $pageResource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * PageManagment constructor.
     * @param PageFactory $pageFactory
     * @param ResourceModel\Page $pageResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Model\ResourceModel\Page $pageResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->pageFactory = $pageFactory;
        $this->pageResource = $pageResource;
        $this->storeManager = $storeManager;
    }

    /**
     * Load page data by given page identifier.
     *
     * @param string $identifier
     * @param int|null $storeId
     * @return \Magento\Cms\Api\Data\PageInterface
     * @throws NoSuchEntityException
     */
    public function getByIdentifier($identifier, $storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $page = $this->pageFactory->create();
        $page->setStoreId($storeId);
        $this->pageResource->load($page, $identifier, PageInterface::IDENTIFIER);

        if (!$page->getId()) {
            throw new NoSuchEntityException(__('CMS Page with identifier "%1" does not exist.', $identifier));
        }

        return $page;
    }
}