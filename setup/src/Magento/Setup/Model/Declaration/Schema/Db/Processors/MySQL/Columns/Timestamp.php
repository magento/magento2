<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\Columns;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * @inheritdoc
 */
class Timestamp implements DbSchemaProcessorInterface
{
    /**
     * @var OnUpdate
     */
    private $onUpdate;

    /**
     * Timestamp constructor.
     * @param OnUpdate $onUpdate
     */
    public function __construct(OnUpdate $onUpdate)
    {
        $this->onUpdate = $onUpdate;
    }

    /**
     * @inheritdoc
     */
    public function toDefinition(ElementInterface $element)
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function fromDefinition(array $data)
    {
        $matches = [];
        if (preg_match('/^(timestamp|datetime)/', $data['type'], $matches)) {
            $data['type'] = $matches[1];
            $data = $this->onUpdate->fromDefinition($data);
        }

        return $data;
    }
}
