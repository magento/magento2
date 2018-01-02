<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Setup\Model\Declaration\Schema\Db\DbDefinitionProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * As all MySQL aliases as BOOL or BOOLEAN are converted to TINYINT(1)
 * proposed to processed tinyint as boolean
 *
 * @inheritdoc
 */
class Boolean implements DbDefinitionProcessorInterface
{
    /**
     * Type with what we will persist column
     */
    const TYPE = 'BOOLEAN';

    /**
     * Type of integer that will be used in MySQL for boolean
     */
    const INTEGER_TYPE = 'tinyint';

    /**
     * Padding for integer described below
     */
    const INTEGER_PADDING = '1';

    /**
     * @var Nullable
     */
    private $nullable;

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param Nullable $nullable
     * @param BooleanUtils $booleanUtils
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Nullable $nullable,
        BooleanUtils $booleanUtils,
        ResourceConnection $resourceConnection
    ) {
        $this->nullable = $nullable;
        $this->booleanUtils = $booleanUtils;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Boolean $column
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $column)
    {
        return sprintf(
            '%s %s %s %s',
            $this->resourceConnection->getConnection()->quoteIdentifier($column->getName()),
            self::TYPE,
            $this->nullable->toDefinition($column),
            $column->getDefault() !== null ?
                sprintf('DEFAULT %s', $column->getDefault() ? 1 : 0) : ''
        );
    }

    /**
     * Boolean is presented as tinyint(1) so we need to detect that value
     *
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        if ($data['type'] === self::INTEGER_TYPE && $data['padding'] === self::INTEGER_PADDING) {
            $data['type'] = strtolower(self::TYPE);
            $data['default'] = (bool) $data['default'];
            $data['unsigned'] = false; //For boolean we always do not want to have unsigned
        }

        return $data;
    }
}
