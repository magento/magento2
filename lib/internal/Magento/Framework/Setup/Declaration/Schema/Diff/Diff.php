<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Diff;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraint;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Index;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use Magento\Framework\Setup\Declaration\Schema\Dto\TableElementInterface;
use Magento\Framework\Setup\Declaration\Schema\ElementHistory;
use Magento\Framework\Setup\Declaration\Schema\ElementHistoryFactory;
use Magento\Framework\Setup\Declaration\Schema\Operations\DropReference;

/**
 * Holds information about all changes between 2 schemas: db and declaration XML.
 * Holds 2 items:
 *  - new (Should be changed to)
 *  - old ()
 * @api
 */
class Diff implements DiffInterface
{
    /**
     * Whitelist file name.
     */
    const GENERATED_WHITELIST_FILE_NAME = 'db_schema_whitelist.json';

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
                    DIRECTORY_SEPARATOR . 'db_schema_whitelist.json';

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
     * be registered due to backward incompatibility
     * Extensibility point: if you want to add some dynamic rules of applying or ignoring any schema elements
     * you can do this by pluginizing this method
     *
     * @param  ElementInterface | Table $object
     * @param string $operation
     * @return bool
     */
    public function canBeRegistered(ElementInterface $object, $operation): bool
    {
        if (!isset($this->destructiveOperations[$operation])) {
            return true;
        }

        $checkResult = false;
        $whiteList = $this->getWhiteListTables();

        if ($object instanceof TableElementInterface) {
            $tableNameWithoutPrefix = $object->getTable()->getNameWithoutPrefix();
            $type = $object->getElementType();

            if ($this->isElementHaveAutoGeneratedName($object)) {
                $checkResult =
                    isset($whiteList[$tableNameWithoutPrefix][$type][$object->getNameWithoutPrefix()]);
            } else {
                $checkResult = isset($whiteList[$tableNameWithoutPrefix][$type][$object->getName()]);
            }
        } elseif ($object instanceof Table) {
            $checkResult = isset($whiteList[$object->getNameWithoutPrefix()]);
        }

        return $checkResult;
    }

    /**
     * Check if the element has an auto-generated name.
     *
     * @param ElementInterface $element
     * @return bool
     */
    private function isElementHaveAutoGeneratedName(ElementInterface $element): bool
    {
        return in_array($element->getElementType(), [Index::TYPE, Constraint::TYPE], true);
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
        ElementInterface $oldDtoObject = null
    ) {
        if (!$this->canBeRegistered($dtoObject, $operation)) {
            return $this;
        }

        $historyData = ['new' => $dtoObject, 'old' => $oldDtoObject];
        $history = $this->elementHistoryFactory->create($historyData);
        //dtoObjects can have 4 types: column, constraint, index, table
        $this->changes[$this->findTableIndex($dtoObject, $operation)][$operation][] = $history;
        $this->debugChanges[$operation][] = $history;
        return $this;
    }

    /**
     * As tables can references to each other, we need to take into account
     * that they should goes in specific structure: parent table -> child table
     * Also we should take into account, that first of all in any case we need to remove all foreign keys
     * from tables and only then modify that tables
     *
     * @param ElementInterface $element
     * @param string $operation
     * @return int
     */
    private function findTableIndex(ElementInterface $element, string $operation) : int
    {
        $elementName = $element instanceof TableElementInterface ?
            $element->getTable()->getName() : $element->getName();
        //We use not real tables but table indexes in order to be sure that order of table is correct
        $tableIndex = $this->tableIndexes[$elementName] ?? INF;
        return $operation === DropReference::OPERATION_NAME ? 0 : (int) $tableIndex;
    }
}
