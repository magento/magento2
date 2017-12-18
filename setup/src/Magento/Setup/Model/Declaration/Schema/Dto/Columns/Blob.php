<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Columns;

use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementDiffAwareInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;

/**
 * This column represent binary type
 * We can have few binary types: blob, mediumblob, largeblog
 * Declared in SQL, like blob
 */
class Blob extends Column implements
    ElementDiffAwareInterface,
    ColumnNullableAwareInterface
{
    /**
     * @var bool
     */
    private $nullable;

    /**
     * @param string $name
     * @param string $elementType
     * @param Table $table
     * @param $nullable
     */
    public function __construct(
        string $name,
        string $elementType,
        Table $table,
        bool $nullable = true
    ) {
        parent::__construct($name, $elementType, $table);
        $this->nullable = $nullable;
    }

    /**
     * Check whether column can be nullable
     *
     * @return bool
     */
    public function isNullable()
    {
        return $this->nullable;
    }

    /**
     * @inheritdoc
     */
    public function getDiffSensitiveParams()
    {
        return [
            'type' => $this->getElementType(),
            'nullable' => $this->isNullable()
        ];
    }
}
