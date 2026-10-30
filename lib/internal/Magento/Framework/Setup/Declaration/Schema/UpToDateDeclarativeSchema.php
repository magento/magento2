<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema;

use Magento\Framework\Setup\Declaration\Schema\Diff\SchemaDiff;
use Magento\Framework\Setup\UpToDateValidatorInterface;
use Magento\Framework\Setup\DetailProviderInterface;

/**
 * Allows to validate if schema is up to date or not
 */
class UpToDateDeclarativeSchema implements UpToDateValidatorInterface, DetailProviderInterface
{
    /**
     * @var SchemaConfigInterface
     */
    private $schemaConfig;

    /**
     * @var SchemaDiff
     */
    private $schemaDiff;

    /**
     * @var array|null
     */
    private $cachedDiff = null;

    /**
     * UpToDateSchema constructor.
     * @param SchemaConfigInterface $schemaConfig
     * @param SchemaDiff $schemaDiff
     */
    public function __construct(
        SchemaConfigInterface $schemaConfig,
        SchemaDiff $schemaDiff
    ) {
        $this->schemaConfig = $schemaConfig;
        $this->schemaDiff = $schemaDiff;
    }

    /**
     * Get the message
     *
     * @return string
     */
    public function getNotUpToDateMessage() : string
    {
        return 'Declarative Schema is not up to date';
    }

    /**
     * Check calculate schema differences
     *
     * @return bool
     */
    public function isUpToDate() : bool
    {
        return empty($this->calculateDiff());
    }

    /**
     * Get detailed information about schema differences
     *
     * @return array
     */
    public function getDetails() : array
    {
        $diffData = $this->calculateDiff();
        $summary = $this->buildSummary($diffData);
        $summary['timestamp'] = date('Y-m-d H:i:s');

        return $summary;
    }

    /**
     * Calculate schema differences and cache the result
     *
     * @return array
     */
    private function calculateDiff() : array
    {
        if ($this->cachedDiff === null) {
            $declarativeSchema = $this->schemaConfig->getDeclarationConfig();
            $dbSchema = $this->schemaConfig->getDbConfig();
            $diff = $this->schemaDiff->diff($declarativeSchema, $dbSchema);
            $this->cachedDiff = $diff->getAll() ?? [];
        }

        return $this->cachedDiff;
    }

    /**
     * Build a summary of schema differences
     *
     * @param array $diffData
     * @return array
     */
    private function buildSummary(array $diffData): array
    {
        $summary = [
            'timestamp' => date('Y-m-d H:i:s'),
            'total_differences' => 0,
            'by_change_type' => [],
            'affected_tables' => [],
            'changes' => []
        ];
        try {
            foreach ($diffData as $operations) {
                if (!is_array($operations)) {
                    continue;
                }
                foreach ($operations as $operationType => $changes) {
                    $this->initChangeTypeCount($summary, $operationType);

                    $changeCount = is_array($changes) ? count($changes) : 1;
                    $summary['by_change_type'][$operationType] += $changeCount;
                    $summary['total_differences'] += $changeCount;

                    if (!is_array($changes)) {
                        continue;
                    }

                    foreach ($changes as $changeIndex => $change) {
                        $changeInfo = $this->buildChangeInfo($change, $operationType, $changeIndex, $summary);
                        $summary['changes'][] = $changeInfo;
                    }
                }
            }
        } catch (\Exception $e) {
            $summary['error'] = $e->getMessage();
        }
        return $summary;
    }

    /**
     * Initialize the counter for a given operation type in the summary if not already set.
     *
     * @param array &$summary
     * @param string $operationType
     */
    private function initChangeTypeCount(array &$summary, string $operationType): void
    {
        if (!isset($summary['by_change_type'][$operationType])) {
            $summary['by_change_type'][$operationType] = 0;
        }
    }

    /**
     * Build a structured array with information about a single change operation.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @param mixed $change
     * @param string $operationType
     * @param int|string $changeIndex
     * @param array $summary
     * @return array
     */
    private function buildChangeInfo($change, $operationType, $changeIndex, &$summary): array
    {
        $changeInfo = [
            'operation' => $operationType,
            'index' => $changeIndex
        ];

        $tableName = $this->safeGetTableName($change);
        if ($tableName) {
            $changeInfo['table'] = $tableName;

            if (!isset($summary['affected_tables'][$tableName])) {
                $summary['affected_tables'][$tableName] = [];
            }
            if (!isset($summary['affected_tables'][$tableName][$operationType])) {
                $summary['affected_tables'][$tableName][$operationType] = 0;
            }
            $summary['affected_tables'][$tableName][$operationType]++;
        }

        if ($change instanceof ElementHistory) {
            $changeInfo = $this->processElementHistory($change, $changeInfo);
        } elseif (is_array($change) && isset($change['name'])) {
            $changeInfo['name'] = $change['name'];
        } elseif (is_object($change) && method_exists($change, 'getName')) {
            $changeInfo['name'] = $change->getName();

            if (method_exists($change, 'getType')) {
                $this->isMethodExists($change, $changeInfo);
            }
        }
        return $changeInfo;
    }

    /**
     * Build a structured array with method exist information.
     *
     * @param mixed $change
     * @param array $changeInfo
     */
    private function isMethodExists(mixed $change, array &$changeInfo): void
    {
        $type = $change->getType();
        if ($type === 'index' || $type === 'constraint') {
            $changeInfo['type'] = $type;
            if (method_exists($change, 'getColumns')) {
                $columns = $change->getColumns();
                if (is_array($columns)) {
                    $changeInfo['columns'] = array_map(function ($column) {
                        if (is_object($column) && method_exists($column, 'getName')) {
                            return $column->getName();
                        }
                        return is_string($column) ? $column : null;
                    }, $columns);
                    // Remove any nulls if any invalid columns found
                    $changeInfo['columns'] = array_filter($changeInfo['columns']);
                }
            }
        }
    }

    /**
     * Safely get table name from any change object
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param mixed $change
     * @return string|null
     */
    private function safeGetTableName($change): ?string
    {
        try {
            // Option 1: ElementHistory with getNew() or getOld()
            if ($change instanceof ElementHistory) {
                $element = $change->getNew() ?: $change->getOld();
                if ($element) {
                    // If element is a table
                    if (method_exists($element, 'getType') && $element->getType() === 'table' &&
                        method_exists($element, 'getName')) {
                        return $element->getName();
                    }

                    // If element belongs to a table
                    if (method_exists($element, 'getTable')) {
                        $table = $element->getTable();
                        if ($table && method_exists($table, 'getName')) {
                            return $table->getName();
                        }
                    }
                }
            }

            // Option 2: Array with 'table' key
            if (is_array($change) && isset($change['table'])) {
                return $change['table'];
            }

            // Option 3: Object with getTable() method
            if (is_object($change) && method_exists($change, 'getTable')) {
                $table = $change->getTable();
                if (is_string($table)) {
                    return $table;
                } elseif (is_object($table) && method_exists($table, 'getName')) {
                    return $table->getName();
                }
            }

            // Option 4: Object is itself a table
            if (is_object($change) && method_exists($change, 'getType') &&
                $change->getType() === 'table' && method_exists($change, 'getName')) {
                return $change->getName();
            }
        } catch (\Exception $e) {
            // Silently fail and return null
            error_log('Error get table name: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Process ElementHistory object to extract useful information
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param ElementHistory $change
     * @param array $changeInfo
     * @return array
     */
    private function processElementHistory($change, array $changeInfo): array
    {
        try {
            $newElement = $change->getNew();
            $oldElement = $change->getOld();

            // Get element name
            if ($newElement && method_exists($newElement, 'getName')) {
                $changeInfo['name'] = $newElement->getName();
            } elseif ($oldElement && method_exists($oldElement, 'getName')) {
                $changeInfo['name'] = $oldElement->getName();
            }

            // Get element type
            if ($newElement && method_exists($newElement, 'getType')) {
                $changeInfo['type'] = $newElement->getType();
            } elseif ($oldElement && method_exists($oldElement, 'getType')) {
                $changeInfo['type'] = $oldElement->getType();
            }

            // For modify operations, add basic diff information
            if (($changeInfo['operation'] === 'modify_column' || $changeInfo['operation'] === 'modify_table')
                && $oldElement && $newElement) {
                // Check for comment differences (most common issue)
                if (method_exists($oldElement, 'getComment') && method_exists($newElement, 'getComment')) {
                    $oldComment = $oldElement->getComment();
                    $newComment = $newElement->getComment();

                    if ($oldComment !== $newComment) {
                        $changeInfo['comment_changed'] = true;
                        $changeInfo['old_comment'] = $oldComment;
                        $changeInfo['new_comment'] = $newComment;
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail and return original changeInfo
            error_log('Error processing element history: ' . $e->getMessage());
        }

        return $changeInfo;
    }
}
