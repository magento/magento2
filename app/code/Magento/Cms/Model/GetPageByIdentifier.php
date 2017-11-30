<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GetPageByIdentifier
 */
class GetPageByIdentifier implements GetPageByIdentifierInterface
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
     * PageManagement constructor.
     * @param PageFactory $pageFactory
     * @param ResourceModel\Page $pageResource
     */
    public function __construct(
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Model\ResourceModel\Page $pageResource
    ) {
        $this->pageFactory = $pageFactory;
        $this->pageResource = $pageResource;
    }

    /**
     * Load page data by given page identifier.
     *
     * @param string $identifier
     * @param int $storeId
     * @return PageInterface
     * @throws NoSuchEntityException
     */
    public function execute(string $identifier, int $storeId) : PageInterface
    {
        $page = $this->pageFactory->create();
        $page->setStoreId($storeId);
        $this->pageResource->load($page, $identifier, PageInterface::IDENTIFIER);

        if (!$page->getId()) {
            throw new NoSuchEntityException(__('CMS Page with identifier "%1" does not exist.', $identifier));
        }

        return $page;
    }
}

