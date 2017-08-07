<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Sequence;

use Magento\Framework\DB\Sequence\SequenceInterface;

/**
 * Class SequenceRegistry
 * @since 2.1.0
 */
class SequenceRegistry
{
    /**
     * @var array
     * @since 2.1.0
     */
    private $registry;

    /**
     * Register information about existing sequence
     *
     * @param string $entityType
     * @param SequenceInterface|null $sequence
     * @param string|null $sequenceTable
     * @return void
     * @since 2.1.0
     */
    public function register($entityType, $sequence = null, $sequenceTable = null)
    {
        $this->registry[$entityType]['sequence'] = $sequence;
        $this->registry[$entityType]['sequenceTable'] = $sequenceTable;
    }

    /**
     * Returns sequence information
     *
     * @param string $entityType
     * @return bool|array
     * @since 2.1.0
     */
    public function retrieve($entityType)
    {
        if (isset($this->registry[$entityType])) {
            return $this->registry[$entityType];
        }
        return false;
    }
}
