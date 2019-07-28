<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Ui\Component\DataProvider\Operation\Failed;

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
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * SearchResult constructor.
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param IdentifierResolver $identifierResolver
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param string $mainTable
     * @param null $resourceModel
     * @param string $identifierName identifier field name for collection items
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        IdentifierResolver $identifierResolver,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        $mainTable = 'magento_operation',
        $resourceModel = null,
        $identifierName = 'id'
    ) {
        $this->jsonHelper = $jsonHelper;
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
        $this->getSelect()->from(['main_table' => $this->getMainTable()], ['id', 'result_message', 'serialized_data'])
            ->where('bulk_uuid=?', $bulkUuid)
            ->where('status=?', OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        foreach ($this->_items as $key => $item) {
            try {
                $unserializedData = $this->jsonHelper->jsonDecode($item['serialized_data']);
            } catch (\Exception $e) {
                $this->_logger->error($e->getMessage());
                $unserializedData = [];
            }
            $this->_items[$key]->setData('meta_information', $this->provideMetaInfo($unserializedData));
            $this->_items[$key]->setData('link', $this->getLink($unserializedData));
            $this->_items[$key]->setData('entity_id', $this->getEntityId($unserializedData));
        }
        return $this;
    }

    /**
     * Provide meta info by serialized data
     *
     * @param array $item
     * @return string
     */
    private function provideMetaInfo($item)
    {
        $metaInfo = '';
        if (isset($item['meta_information'])) {
            $metaInfo = $item['meta_information'];
        }
        return $metaInfo;
    }

    /**
     * Get link from serialized data
     *
     * @param array $item
     * @return string
     */
    private function getLink($item)
    {
        $entityLink = '';
        if (isset($item['entity_link'])) {
            $entityLink = $item['entity_link'];
        }
        return $entityLink;
    }

    /**
     * Get entity id from serialized data
     *
     * @param array $item
     * @return string
     */
    private function getEntityId($item)
    {
        $entityLink = '';
        if (isset($item['entity_id'])) {
            $entityLink = $item['entity_id'];
        }
        return $entityLink;
    }
}
