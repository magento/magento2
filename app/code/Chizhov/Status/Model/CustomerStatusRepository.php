<?php

declare(strict_types=1);

namespace Chizhov\Status\Model;

use Chizhov\Status\Api\CustomerStatusRepositoryInterface;
use Chizhov\Status\Api\Data\CustomerStatusInterface;
use Chizhov\Status\Api\Data\CustomerStatusSearchResultsInterface;
use Chizhov\Status\Model\CustomerStatus\Command\DeleteByIdInterface;
use Chizhov\Status\Model\CustomerStatus\Command\GetInterface;
use Chizhov\Status\Model\CustomerStatus\Command\GetListInterface;
use Chizhov\Status\Model\CustomerStatus\Command\SaveInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

class CustomerStatusRepository implements CustomerStatusRepositoryInterface
{
    /**
     * @var \Chizhov\Status\Model\CustomerStatus\Command\SaveInterface
     */
    protected $commandSave;

    /**
     * @var \Chizhov\Status\Model\CustomerStatus\Command\GetInterface
     */
    protected $commandGet;

    /**
     * @var \Chizhov\Status\Model\CustomerStatus\Command\GetListInterface
     */
    protected $commandGetList;

    /**
     * @var \Chizhov\Status\Model\CustomerStatus\Command\DeleteByIdInterface
     */
    protected $commandDeleteById;

    /**
     * CustomerStatusRepository constructor.
     *
     * @param \Chizhov\Status\Model\CustomerStatus\Command\SaveInterface $commandSave
     * @param \Chizhov\Status\Model\CustomerStatus\Command\GetInterface $commandGet
     * @param \Chizhov\Status\Model\CustomerStatus\Command\GetListInterface $commandGetList
     * @param \Chizhov\Status\Model\CustomerStatus\Command\DeleteByIdInterface $commandDeleteById
     */
    public function __construct(
        SaveInterface $commandSave,
        GetInterface $commandGet,
        GetListInterface $commandGetList,
        DeleteByIdInterface $commandDeleteById
    ) {
        $this->commandSave = $commandSave;
        $this->commandGet = $commandGet;
        $this->commandGetList = $commandGetList;
        $this->commandDeleteById = $commandDeleteById;
    }

    /**
     * @inheritDoc
     */
    public function save(CustomerStatusInterface $status): int
    {
        return $this->commandSave->execute($status);
    }

    /**
     * @inheritDoc
     */
    public function get(int $customerId): CustomerStatusInterface
    {
        return $this->commandGet->execute($customerId);
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null): CustomerStatusSearchResultsInterface
    {
        return $this->commandGetList->execute($searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $customerId): void
    {
        $this->commandDeleteById->execute($customerId);
    }
}
