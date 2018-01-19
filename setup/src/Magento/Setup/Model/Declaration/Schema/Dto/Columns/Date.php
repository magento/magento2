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
 * Date column
 * Declared in SQL, like DATE
 * Doesnt have any additional params
 * Is represented like: YY:MM:DD
 */
class Date extends Column
    implements ElementDiffAwareInterface,
        ColumnNullableAwareInterface
{
    /**
     * @var bool
     */
    private $nullable;

    /**
     * @param string $name
     * @param string $type
     * @param Table $table
     * @param bool $nullable
     * @param string|null $comment
     * @param string|null $onCreate
     */
    public function __construct(
        string $name,
        string $type,
        Table $table,
        bool $nullable = true,
        string $comment = null,
        string $onCreate = null
    ) {
        parent::__construct($name, $type, $table, $comment, $onCreate);
        $this->nullable = $nullable;
    }

    /**
     * @inheritdoc
     */
    public function getDiffSensitiveParams()
    {
        return [
            'type' => $this->getType(),
            'nullable' => $this->isNullable()
        ];
    }

    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }
}
