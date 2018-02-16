<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Diff;

use Magento\Developer\Console\Command\TablesWhitelistGenerateCommand;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;
use Magento\Setup\Model\Declaration\Schema\Dto\TableElementInterface;
use Magento\Setup\Model\Declaration\Schema\ElementHistory;
use Magento\Setup\Model\Declaration\Schema\ElementHistoryFactory;

/**
 * Holds information about all changes between 2 schemas: db and declaration XML.
 * Holds 2 items:
 *  - new (Should be changed to)
 *  - old ()
 */
class Diff implements DiffInterface
{
    /**
     * @var array
     */
    private $changes;

    /**
     * This changes created only for debug reasons.
     *
     * @var array
     */
    public $debugChanges;

    /**
     * @var array
     */
    private $whiteListTables = [];

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @var ElementHistoryFactory
     */
    private $elementHistoryFactory;

    /**
     * This indexes is needed to ensure that sort order in which table operations
     * will be executed is correct.
     *
     * @var array
     */
    private $tableIndexes;

    /**
     * List of operations that are destructive from the point of declarative setup
     * and can make system unstable, for example DropTable.
     *
     * @var string[]
     */
    private $destructiveOperations;

    /**
     * Constructor.
     *
     * @param ComponentRegistrar $componentRegistrar
     * @param ElementHistoryFactory $elementHistoryFactory
     * @param array $tableIndexes
     * @param array $destructiveOperations
     */
    public function __construct(
        ComponentRegistrar $componentRegistrar,
        ElementHistoryFactory $elementHistoryFactory,
        array $tableIndexes,
        array $destructiveOperations
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->elementHistoryFactory = $elementHistoryFactory;
        $this->tableIndexes = $tableIndexes;
        $this->destructiveOperations = $destructiveOperations;
    }

    /**
     * We return all sorted changes.
     *
     * All changes are sorted because there are dependencies between tables, like foreign keys.
     *
     * @inheritdoc
     */
    public function getAll()
    {
        if ($this->changes) {
            ksort($this->changes);
        }
        return $this->changes;
    }

    /**
     * Retrieve all changes for specific table.
     *
     * @param string $table
     * @param string $operation
     * @return ElementHistory[]
     */
    public function getChange($table, $operation)
    {
        $tableIndex = $this->tableIndexes[$table];
        return $this->changes[$tableIndex][$operation] ?? [];
    }

    /**
     * Retrieve array of whitelisted tables.
     * Whitelist tables should have JSON format and should be added through
     * CLI command: should be done in next story.
     *
     * @return array
     */
    private function getWhiteListTables()
    {
        if (!$this->whiteListTables) {
            foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $path) {
                $whiteListPath = $path . DIRECTORY_SEPARATOR . 'etc' .
                    DIRECTORY_SEPARATOR . TablesWhitelistGenerateCommand::GENERATED_FILE_NAME;

                if (file_exists($whiteListPath)) {
                    $this->whiteListTables = array_replace_recursive(
                        $this->whiteListTables,
                        json_decode(file_get_contents($whiteListPath), true)
                    );
                }
            }
        }

        return $this->whiteListTables;
    }

    /**
     * Check whether element can be registered.
     *
     * For example, if element is not in db_schema_whitelist.json it cant
     * be registered due to backward incompatibility.
     *
     * @param  ElementInterface | Table $object
     * @param string $operation
     * @return bool
     */
    private function canBeRegistered(ElementInterface $object, $operation)
    {
        if (!isset($this->destructiveOperations[$operation])) {
            return true;
        }

        $whiteList = $this->getWhiteListTables();
        $type = $object->getElementType();

        if ($object instanceof TableElementInterface) {
            return isset($whiteList[$object->getTable()->getNameWithoutPrefix()][$type][$object->getName()]);
        }

        return isset($whiteList[$object->getNameWithoutPrefix()]);
    }

    /**
     * Register DTO object.
     *
     * @param TableElementInterface $dtoObject
     * @inheritdoc
     */
    public function register(
        ElementInterface $dtoObject,
        $operation,
        ElementInterface $oldDtoObject = null,
        $tableKey = null
    ) {
        if (!$this->canBeRegistered($dtoObject, $operation)) {
            return $this;
        }

        $historyData = ['new' => $dtoObject, 'old' => $oldDtoObject];
        $history = $this->elementHistoryFactory->create($historyData);
        $dtoObjectName = $dtoObject instanceof TableElementInterface ?
            $dtoObject->getTable()->getName() : $dtoObject->getName();
        //We use not real tables but table indexes in order to be sure that order of table is correct
        $tableKey = $tableKey === null ? $this->tableIndexes[$dtoObjectName] : $this->tableIndexes[$tableKey];
        //dtoObjects can have 4 types: column, constraint, index, table
        $this->changes[$tableKey][$operation][] = $history;
        $this->debugChanges[$operation][] = $history;
        return $this;
    }
}
