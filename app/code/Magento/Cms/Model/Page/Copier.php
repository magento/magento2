<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Page;

use Magento\Framework\Exception\LocalizedException;
use Magento\Cms\Api\Data\PageInterface;

/**
 * The purpose of this class is duplicate existing cms page
 */
class Copier
{
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    private $pageFactory;

    /**
     * @var \Magento\Cms\Api\PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * Copier constructor.
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Cms\Api\PageRepositoryInterface $pageRepository
     */
    public function __construct(
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Api\PageRepositoryInterface $pageRepository
    ) {
        $this->pageFactory = $pageFactory;
        $this->pageRepository = $pageRepository;
    }

    /**
     * Duplicate existing Cms Page
     *
     * @param PageInterface $model
     * @return PageInterface
     * @throws LocalizedException
     */
    public function copy(PageInterface $model)
    {
        $data = $model->getData();
        $newPage = $this->pageFactory->create()->setData($data);
        //add unique identifier - url key
        $newPage->setId(null);
        $identifier = $newPage->getIdentifier() .  '-' . uniqid();
        $title = $newPage->getTitle() . '-1';
        $newPage->setIdentifier($identifier);
        $newPage->setIsActive(false);
        $newPage->setTitle($title);
        return $this->pageRepository->save($newPage);
    }
}
