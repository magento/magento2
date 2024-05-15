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
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\HydratorInterface;

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
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @param PageRepositoryInterface $repository
     * @param ValidatorInterface[] $validators
     * @param HydratorInterface|null $hydrator
     */
    public function __construct(
        PageRepositoryInterface $repository,
        array $validators = [],
        ?HydratorInterface $hydrator = null
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
        $this->hydrator = $hydrator ?? ObjectManager::getInstance()->get(HydratorInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function save(PageInterface $page)
    {
        if ($page->getId()) {
            $page = $this->hydrator->hydrate($this->getById($page->getId()), $this->hydrator->extract($page));
        }
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
