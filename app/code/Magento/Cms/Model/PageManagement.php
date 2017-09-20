<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PageManagement
 */
class PageManagement implements PageManagementInterface
{
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    private $pageFactory;

    /**
     * @var ResourceModel\Page
     */
    private $pageResource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * PageManagement constructor.
     * @param PageFactory $pageFactory
     * @param ResourceModel\Page $pageResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Model\ResourceModel\Page $pageResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->pageFactory = $pageFactory;
        $this->pageResource = $pageResource;
        $this->storeManager = $storeManager;
    }

    /**
     * Load page data by given page identifier.
     *
     * @param string $identifier
     * @param int|null $storeId
     * @return PageInterface
     * @throws NoSuchEntityException
     */
    public function getByIdentifier(string $identifier, $storeId = null) : PageInterface
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

