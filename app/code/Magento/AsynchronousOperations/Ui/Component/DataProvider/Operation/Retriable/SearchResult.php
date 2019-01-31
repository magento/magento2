<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Ui\Component\DataProvider\Operation\Retriable;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;
use Magento\AsynchronousOperations\Ui\Component\DataProvider\Bulk\IdentifierResolver;
use Magento\Framework\Bulk\OperationInterface;

/**
 * Class SearchResult
 */
class SearchResult extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var IdentifierResolver
     */
    private $identifierResolver;

    /**
     * SearchResult constructor.
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param IdentifierResolver $identifierResolver
     * @param string $mainTable
     * @param null $resourceModel
     * @param string $identifierName
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        IdentifierResolver $identifierResolver,
        $mainTable = 'magento_operation',
        $resourceModel = null,
        $identifierName = 'id'
    ) {
        $this->identifierResolver = $identifierResolver;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel,
            $identifierName
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        $bulkUuid = $this->identifierResolver->execute();
        $this->getSelect()->from(['main_table' => $this->getMainTable()], ['id', 'result_message', 'error_code'])
            ->where('bulk_uuid=?', $bulkUuid)
            ->where('status=?', OperationInterface::STATUS_TYPE_RETRIABLY_FAILED)
            ->group('error_code')
            ->columns(['records_qty' => new \Zend_Db_Expr('COUNT(id)')]);
        return $this;
    }
}
