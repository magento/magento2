<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Adapter\Aggregation;

use Magento\Framework\Search\RequestInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Composite request checker.
 */
class RequestCheckerComposite implements RequestCheckerInterface
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RequestCheckerInterface[]
     */
    private $queryCheckers;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param RequestCheckerInterface[] $queryCheckers
     * @throws \InvalidArgumentException
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        array $queryCheckers
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->queryCheckers = $queryCheckers;

        foreach ($this->queryCheckers as $queryChecker) {
            if (!$queryChecker instanceof RequestCheckerInterface) {
                throw new \InvalidArgumentException(
                    get_class($queryChecker) .
                    ' does not implement ' .
                    \Magento\CatalogSearch\Model\Adapter\Aggregation\RequestCheckerInterface::class
                );
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function isApplicable(RequestInterface $request)
    {
        $result = true;

        foreach ($this->queryCheckers as $item) {
            /** @var RequestCheckerInterface $item */
            $result = $item->isApplicable($request);
            if (!$result) {
                break;
            }
        }

        return $result;
    }
}
