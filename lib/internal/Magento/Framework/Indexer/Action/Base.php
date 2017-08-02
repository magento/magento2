<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Action;

use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\App\ResourceConnection\SourceProviderInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Stdlib\StringUtils as StdString;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Indexer\FieldsetPool;
use Magento\Framework\Indexer\HandlerPool;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\SaveHandlerFactory;
use Magento\Framework\App\ResourceConnection\SourceFactory;
use Magento\Framework\Indexer\HandlerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @since 2.0.0
 */
class Base implements ActionInterface
{
    /**
     * Prefix
     */
    const PREFIX = 'index_';

    /**
     * @var FieldsetPool
     * @since 2.0.0
     */
    protected $fieldsetPool;

    /**
     * @var AdapterInterface
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $connection;

    /**
     * @var SourceProviderInterface[]
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $sources;

    /**
     * @var SourceProviderInterface
     * @since 2.0.0
     */
    protected $primarySource;

    /**
     * @var HandlerInterface[]
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $handlers;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $data;

    /**
     * @var array
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $columnTypesMap = [
        'varchar'    => ['type' => Table::TYPE_TEXT, 'size' => 255],
        'mediumtext' => ['type' => Table::TYPE_TEXT, 'size' => 16777216],
        'text'       => ['type' => Table::TYPE_TEXT, 'size' => 65536],
    ];

    /**
     * @var array
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $filterColumns;

    /**
     * @var array
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $searchColumns;

    /**
     * @var SourceFactory
     * @since 2.0.0
     */
    protected $sourceFactory;

    /**
     * @var HandlerPool
     * @since 2.0.0
     */
    protected $handlerPool;

    /**
     * @var SaveHandlerFactory
     * @since 2.0.0
     */
    protected $saveHandlerFactory;

    /**
     * @var String
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $string;

    /**
     * @var IndexStructureInterface
     * @since 2.0.0
     */
    protected $indexStructure;

    /**
     * @var array
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $filterable = [];

    /**
     * @var array
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $searchable = [];

    /**
     * @var IndexerInterface
     * @since 2.0.0
     */
    protected $saveHandler;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $tableAlias = 'main_table';

    /**
     * @param AppResource $resource
     * @param SourceFactory $sourceFactory
     * @param HandlerPool $handlerPool
     * @param SaveHandlerFactory $saveHandlerFactory
     * @param FieldsetPool $fieldsetPool
     * @param StdString $string
     * @param IndexStructureInterface $indexStructure
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        AppResource $resource,
        SourceFactory $sourceFactory,
        HandlerPool $handlerPool,
        SaveHandlerFactory $saveHandlerFactory,
        FieldsetPool $fieldsetPool,
        StdString $string,
        IndexStructureInterface $indexStructure,
        $data = []
    ) {
        $this->connection = $resource->getConnection();
        $this->fieldsetPool = $fieldsetPool;
        $this->data = $data;
        $this->sourceFactory = $sourceFactory;
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
     * @since 2.0.0
     */
    protected function execute(array $ids = [])
    {
        $this->prepareFields();
        if (!count($ids)) {
            $this->getSaveHandler()->cleanIndex([]);
        }
        $this->getSaveHandler()->deleteIndex([], new \ArrayObject($ids));
        $this->getSaveHandler()->saveIndex([], $this->prepareDataSource($ids));
    }

    /**
     * Execute full indexation
     *
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function executeRow($id)
    {
         $this->execute([$id]);
    }

    /**
     * Prepare select query
     *
     * @param array|int|null $ids
     * @return SourceProviderInterface
     * @since 2.0.0
     */
    protected function prepareDataSource(array $ids = [])
    {
        return !count($ids)
            ? $this->createResultCollection()
            : $this->createResultCollection()->addFieldToFilter($this->getPrimaryResource()->getIdFieldname(), $ids);
    }

    /**
     * Return index table name
     *
     * @return string
     * @since 2.0.0
     */
    protected function getTableName()
    {
        return self::PREFIX . $this->getPrimaryResource()->getMainTable();
    }

    /**
     * Return save handler
     *
     * @return IndexerInterface
     * @since 2.0.0
     */
    protected function getSaveHandler()
    {
        if ($this->saveHandler === null) {
            $this->saveHandler = $this->saveHandlerFactory->create(
                $this->data['saveHandler'],
                [
                    'indexStructure' => $this->indexStructure,
                    'data' => $this->data,
                ]
            );
        }
        return $this->saveHandler;
    }

    /**
     * Return primary source provider
     *
     * @return SourceProviderInterface
     * @since 2.0.0
     */
    protected function getPrimaryResource()
    {
        return $this->getPrimaryFieldset()['source'];
    }

    /**
     * Return primary fieldset
     *
     * @return []
     * @since 2.0.0
     */
    protected function getPrimaryFieldset()
    {
        return $this->data['fieldsets'][0];
    }

    /**
     * Create select from indexer configuration
     *
     * @return SourceProviderInterface
     * @since 2.0.0
     */
    protected function createResultCollection()
    {
        $select = $this->getPrimaryResource()->getSelect();
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);
        $select->columns($this->getPrimaryResource()->getIdFieldName());
        foreach ($this->data['fieldsets'] as $fieldset) {
            if (isset($fieldset['references'])) {
                foreach ($fieldset['references'] as $reference) {
                    $source = $fieldset['source'];
                    /** @var SourceProviderInterface $source */
                    $currentEntityName = $source->getMainTable();
                    $alias = $this->getPrimaryFieldset()['name'] == $reference['fieldset']
                        ? $this->tableAlias
                        : $reference['fieldset'];
                    $select->joinLeft(
                        [$fieldset['name'] => $currentEntityName],
                        new \Zend_Db_Expr(
                            $fieldset['name'] . '.' . $reference['from'] . '=' . $alias . '.' . $reference['to']
                        ),
                        null
                    );
                }
            }
            foreach ($fieldset['fields'] as $field) {
                $handler = $field['handler'];
                /** @var HandlerInterface $handler */
                $handler->prepareSql(
                    $this->getPrimaryResource(),
                    $this->getPrimaryFieldset()['name'] == $fieldset['name'] ? $this->tableAlias : $fieldset['name'],
                    $field
                );
            }
        }

        return $this->getPrimaryResource();
    }

    /**
     * Prepare configuration data
     *
     * @return void
     * @since 2.0.0
     */
    protected function prepareFields()
    {
        foreach ($this->data['fieldsets'] as $fieldsetName => $fieldset) {
            $this->data['fieldsets'][$fieldsetName]['source'] = $this->sourceFactory->create($fieldset['source']);
            if (isset($fieldset['provider'])) {
                $fieldsetObject = $this->fieldsetPool->get($fieldset['provider']);
                $this->data['fieldsets'][$fieldsetName] =
                    $fieldsetObject->addDynamicData($this->data['fieldsets'][$fieldsetName]);
            }
            foreach ($this->data['fieldsets'][$fieldsetName]['fields'] as $fieldName => $field) {
                $this->data['fieldsets'][$fieldsetName]['fields'][$fieldName]['origin'] =
                    $this->data['fieldsets'][$fieldsetName]['fields'][$fieldName]['origin']
                        ?: $this->data['fieldsets'][$fieldsetName]['fields'][$fieldName]['name'];
                if ($fieldsetName != 0) {
                    $this->data['fieldsets'][$fieldsetName]['fields'][$fieldName]['name'] =
                        $this->data['fieldsets'][$fieldsetName]['name'] . '_'
                        . $this->data['fieldsets'][$fieldsetName]['fields'][$fieldName]['name'];
                }
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
     *
     * @deprecated 2.2.0
     * @since 2.0.0
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
