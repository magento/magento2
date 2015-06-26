<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Action;

use Magento\Framework\App\Resource as AppResource;
use Magento\Framework\App\Resource\SourceProviderInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\IndexerInterface;
use Magento\Framework\Stdlib\String as StdString;
use Magento\Indexer\Model\ActionInterface;
use Magento\Indexer\Model\FieldsetPool;
use Magento\Indexer\Model\HandlerPool;
use Magento\Indexer\Model\IndexStructure;
use Magento\Indexer\Model\SaveHandlerFactory;
use Magento\Framework\App\Resource\SourcePool;
use Magento\Indexer\Model\HandlerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Base implements ActionInterface
{
    /**
     * Prefix
     */
    const PREFIX = 'index_';

    /**
     * @var FieldsetPool
     */
    protected $fieldsetPool;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var SourceProviderInterface[]
     */
    protected $sources;

    /**
     * @var SourceProviderInterface
     */
    protected $primarySource;

    /**
     * @var HandlerInterface[]
     */
    protected $handlers;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $columnTypesMap = [
        'varchar'    => ['type' => Table::TYPE_TEXT, 'size' => 255],
        'mediumtext' => ['type' => Table::TYPE_TEXT, 'size' => 16777216],
        'text'       => ['type' => Table::TYPE_TEXT, 'size' => 65536],
    ];

    /**
     * @var array
     */
    protected $filterColumns;

    /**
     * @var array
     */
    protected $searchColumns;

    /**
     * @var SourcePool
     */
    protected $sourcePool;

    /**
     * @var HandlerPool
     */
    protected $handlerPool;

    /**
     * @var SaveHandlerFactory
     */
    protected $saveHandlerFactory;

    /**
     * @var String
     */
    protected $string;

    /**
     * @var IndexStructure
     */
    protected $indexStructure;

    /**
     * @var array
     */
    protected $filterable = [];

    /**
     * @var array
     */
    protected $searchable = [];

    /**
     * @var IndexerInterface
     */
    protected $saveHandler;

    /**
     * @param AppResource $resource
     * @param SourcePool $sourcePool
     * @param HandlerPool $handlerPool
     * @param SaveHandlerFactory $saveHandlerFactory
     * @param FieldsetPool $fieldsetPool
     * @param StdString $string
     * @param IndexStructure $indexStructure
     * @param array $data
     */
    public function __construct(
        AppResource $resource,
        SourcePool $sourcePool,
        HandlerPool $handlerPool,
        SaveHandlerFactory $saveHandlerFactory,
        FieldsetPool $fieldsetPool,
        StdString $string,
        IndexStructure $indexStructure,
        $data = []
    ) {
        $this->connection = $resource->getConnection('write');
        $this->fieldsetPool = $fieldsetPool;
        $this->data = $data;
        $this->sourcePool = $sourcePool;
        $this->handlerPool = $handlerPool;
        $this->saveHandlerFactory = $saveHandlerFactory;
        $this->string = $string;
        $this->indexStructure = $indexStructure;
    }

    /**
     * Execute
     *
     * @param null|int|array $ids
     * @return void
     */
    protected function execute($ids = null)
    {
        $this->prepareFields();
        $this->indexStructure->delete($this->getTableName());
        $this->indexStructure->create($this->getTableName(), $this->filterable);
        $this->getSaveHandler()->cleanIndex([]);
        $this->getSaveHandler()->saveIndex([], $this->prepareDataSource($ids));
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->execute();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->execute($id);
    }

    /**
     * Prepare select query
     *
     * @param array|int|null $ids
     * @return SourceProviderInterface
     */
    protected function prepareDataSource($ids = null)
    {
        return $ids === null
            ? $this->createResultCollection()
            : $this->createResultCollection()->addFieldToFilter($this->getPrimaryResource()->getIdFieldname(), $ids);
    }

    /**
     * Return index table name
     *
     * @return string
     */
    protected function getTableName()
    {
        return self::PREFIX . $this->getPrimaryResource()->getMainTable();
    }

    /**
     * Return save handler
     *
     * @return IndexerInterface
     */
    protected function getSaveHandler()
    {
        if ($this->saveHandler === null) {
            $this->saveHandler = $this->saveHandlerFactory->create($this->data['saveHandler'], ['data' => $this->data]);
        }
        return $this->saveHandler;
    }

    /**
     * Return primary source provider
     *
     * @return SourceProviderInterface
     */
    protected function getPrimaryResource()
    {
        return $this->getPrimaryFieldset()['source'];
    }

    /**
     * Return primary fieldset
     *
     * @return []
     */
    protected function getPrimaryFieldset()
    {
        return $this->data['fieldsets'][0];
    }

    /**
     * Create select from indexer configuration
     *
     * @return SourceProviderInterface
     */
    protected function createResultCollection()
    {
        $select = $this->getPrimaryResource()->getSelect();
        $select->columns($this->getPrimaryResource()->getIdFieldName());
        foreach ($this->data['fieldsets'] as $fieldset) {
            if (isset($fieldset['references'])) {
                foreach ($fieldset['references'] as $reference) {
                    $source = $fieldset['source'];
                    $referenceSource = $this->data['fieldsets'][$reference['fieldset']]['source'];
                    /** @var SourceProviderInterface $source */
                    /** @var SourceProviderInterface $referenceSource */
                    $currentEntityName = $source->getMainTable();
                    $select->joinInner(
                        $currentEntityName,
                        new \Zend_Db_Expr(
                            $referenceSource->getMainTable() . '.' . $reference['from']
                            . '=' . $currentEntityName . '.' . $reference['to']
                        ),
                        null
                    );
                }
            }
            foreach ($fieldset['fields'] as $field) {
                $handler = $field['handler'];
                /** @var HandlerInterface $handler */
                $handler->prepareSql($fieldset['source'], $field);
            }
        }

        return $this->getPrimaryResource();
    }

    /**
     * Prepare configuration data
     *
     * @return void
     */
    protected function prepareFields()
    {
        foreach ($this->data['fieldsets'] as $fieldsetName => $fieldset) {
            $this->data['fieldsets'][$fieldsetName]['source'] = $this->sourcePool->get($fieldset['source']);
            if (isset($fieldset['class'])) {
                $fieldsetObject = $this->fieldsetPool->get($fieldset['class']);
                $this->data['fieldsets'][$fieldsetName] = $fieldsetObject->addDynamicData($fieldset);
            }
            foreach ($fieldset['fields'] as $fieldName => $field) {
                $this->saveFieldByType($field);
                $this->data['fieldsets'][$fieldsetName]['fields'][$fieldName]['handler'] =
                    $this->handlerPool->get($field['handler']);
                $this->data['fieldsets'][$fieldsetName]['fields'][$fieldName]['dataType'] =
                    isset($field['dataType']) ? $field['dataType'] : 'varchar';
            }
        }
    }

    /**
     * Save field by type
     *
     * @param array $field
     * @return void
     */
    protected function saveFieldByType($field)
    {
        switch ($field['type']) {
            case 'filterable':
                $this->filterable[] = $field;
                break;
            case 'searchable':
                $this->searchable[] = $field;
                break;
        }
    }
}
