<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Service;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class CurrentCategory
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CategoryRepositoryInterface
     */
    private $repository;

    /**
     * CurrentCategory constructor.
     * @param RequestInterface $request
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        RequestInterface $request,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->request = $request;
        $this->repository = $categoryRepository;
    }

    /**
     * @param null $storeId
     * @return CategoryInterface|Category
     * @throws NoSuchEntityException
     */
    public function get($storeId = null): CategoryInterface
    {
        return $this->repository->get((int) $this->request->getParam('id'), $storeId);
    }
}
