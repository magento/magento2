<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview\View\ChangeLogBatchWalker;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManager\ContextInterface;

class IdsContext implements ContextInterface
{
    /**
     * @var \Magento\Framework\Mview\View\ChangeLogBatchWalker\IdsTableBuilderInterface
     */
    private IdsTableBuilderInterface $tableBuilder;
    /**
     * @var \Magento\Framework\Mview\View\ChangeLogBatchWalker\IdsSelectBuilderInterface
     */
    private IdsSelectBuilderInterface $selectBuilder;
    /**
     * @var \Magento\Framework\Mview\View\ChangeLogBatchWalker\IdsFetcherInterface
     */
    private IdsFetcherInterface $fetcher;

    /**
     * @param \Magento\Framework\Mview\View\ChangeLogBatchWalker\IdsTableBuilderInterface|null $tableBuilder
     * @param \Magento\Framework\Mview\View\ChangeLogBatchWalker\IdsSelectBuilderInterface|null $selectBuilder
     * @param \Magento\Framework\Mview\View\ChangeLogBatchWalker\IdsFetcherInterface|null $fetcher
     */
    public function __construct(
        IdsTableBuilderInterface  $tableBuilder = null,
        IdsSelectBuilderInterface $selectBuilder = null,
        IdsFetcherInterface       $fetcher = null
    ) {
        $this->tableBuilder = $tableBuilder ?: ObjectManager::getInstance()->get(IdsTableBuilder::class);
        $this->selectBuilder = $selectBuilder ?: ObjectManager::getInstance()->get(IdsSelectBuilder::class);
        $this->fetcher = $fetcher ?: ObjectManager::getInstance()->get(IdsFetcher::class);
    }

    /**
     * Get table builder
     *
     * @return \Magento\Framework\Mview\View\ChangeLogBatchWalker\IdsTableBuilderInterface
     */
    public function getTableBuilder(): IdsTableBuilderInterface
    {
        return $this->tableBuilder;
    }

    /**
     * Get select builder
     *
     * @return \Magento\Framework\Mview\View\ChangeLogBatchWalker\IdsSelectBuilderInterface
     */
    public function getSelectBuilder(): IdsSelectBuilderInterface
    {
        return $this->selectBuilder;
    }

    /**
     * Get Ids fetcher
     *
     * @return \Magento\Framework\Mview\View\ChangeLogBatchWalker\IdsFetcherInterface
     */
    public function getFetcher(): IdsFetcherInterface
    {
        return $this->fetcher;
    }
}
