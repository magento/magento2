<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Model\Resource\Bookmark;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Resource\Db\Collection\AbstractCollection;
use Magento\Ui\Model\Resource\Bookmark;
use Psr\Log\LoggerInterface;

/**
 * Bookmark Collection
 */
class Collection extends AbstractCollection
{
    /**
     * User context
     *
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @param UserContextInterface $userContext
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param Bookmark $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Bookmark $resource,
        UserContextInterface $userContext
    ) {
        $this->userContext = $userContext;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            null,
            $resource
        );
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Ui\Model\Bookmark', 'Magento\Ui\Model\Resource\Bookmark');
    }

    /**
     * Filtered current bookmark by identifier for current user
     *
     * @param string $identifier
     * @return $this
     */
    public function filterCurrentForIdentifier($identifier)
    {
        $this->addFieldToFilter('user_id', $this->userContext->getUserId())
            ->addFieldToFilter('identifier', $identifier)
            ->addFieldToFilter('current', 1);
        return $this;
    }
}
