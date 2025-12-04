<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\EntityManager\Sequence;

use Magento\Framework\DB\Sequence\SequenceInterface;

/**
 * Class SequenceRegistry
 */
class SequenceRegistry
{
    /**
     * @var array
     */
    private $registry;

    /**
     * Register information about existing sequence
     *
     * @param string $entityType
     * @param SequenceInterface|null $sequence
     * @param string|null $sequenceTable
     * @return void
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
     */
    public function retrieve($entityType)
    {
        if (isset($this->registry[$entityType])) {
            return $this->registry[$entityType];
        }
        return false;
    }
}
