<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\PageRepository;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Validates and saves a page
 */
class ValidationComposite implements PageRepositoryInterface
{
    /**
     * @var PageRepositoryInterface
     */
    private $repository;

    /**
     * @var array
     */
    private $validators;

    /**
     * @param PageRepositoryInterface $repository
     * @param ValidatorInterface[] $validators
     */
    public function __construct(
        PageRepositoryInterface $repository,
        array $validators = []
    ) {
        foreach ($validators as $validator) {
            if (!$validator instanceof ValidatorInterface) {
                throw new \InvalidArgumentException(
                    sprintf('Supplied validator does not implement %s', ValidatorInterface::class)
                );
            }
        }
        $this->repository = $repository;
        $this->validators = $validators;
    }

    /**
     * @inheritdoc
     */
    public function save(PageInterface $page)
    {
        foreach ($this->validators as $validator) {
            $validator->validate($page);
        }

        return $this->repository->save($page);
    }

    /**
     * @inheritdoc
     */
    public function getById($pageId)
    {
        return $this->repository->getById($pageId);
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        return $this->repository->getList($searchCriteria);
    }

    /**
     * @inheritdoc
     */
    public function delete(PageInterface $page)
    {
        return $this->repository->delete($page);
    }

    /**
     * @inheritdoc
     */
    public function deleteById($pageId)
    {
        return $this->repository->deleteById($pageId);
    }
}
