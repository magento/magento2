<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Model\Resource\Bookmark;

use Magento\Framework\Model\Resource\Db\Collection\AbstractCollection;

/**
 * Bookmark Collection
 */
class Collection extends AbstractCollection
{
    /**
     * User context
     *
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Ui\Model\Resource\Bookmark $resource
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Ui\Model\Resource\Bookmark $resource
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
        $this->addFieldToFilter('user_id', 1)//@fixme $this->userContext->getUserId())
            ->addFieldToFilter('identifier', $identifier)
            ->addFieldToFilter('current', 1);
        return $this;
    }
}
