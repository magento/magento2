<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Model;

use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\DB\Sequence\SequenceInterface;

/**
 * Class Sequence represents sequence in logic
 *
 * @api
 * @since 2.0.0
 */
class Sequence implements SequenceInterface
{
    /**
     * Default pattern for Sequence
     */
    const DEFAULT_PATTERN  = "%s%'.09d%s";

    /**
     * @var string
     * @since 2.0.0
     */
    private $lastIncrementId;

    /**
     * @var Meta
     * @since 2.0.0
     */
    private $meta;

    /**
     * @var false|\Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.0.0
     */
    private $connection;

    /**
     * @var string
     * @since 2.0.0
     */
    private $pattern;

    /**
     * @param Meta $meta
     * @param AppResource $resource
     * @param string $pattern
     * @since 2.0.0
     */
    public function __construct(
        Meta $meta,
        AppResource $resource,
        $pattern = self::DEFAULT_PATTERN
    ) {
        $this->meta = $meta;
        $this->connection = $resource->getConnection('sales');
        $this->pattern = $pattern;
    }

    /**
     * Retrieve current value
     *
     * @return string
     * @since 2.0.0
     */
    public function getCurrentValue()
    {
        if (!isset($this->lastIncrementId)) {
            return null;
        }

        return sprintf(
            $this->pattern,
            $this->meta->getActiveProfile()->getPrefix(),
            $this->calculateCurrentValue(),
            $this->meta->getActiveProfile()->getSuffix()
        );
    }

    /**
     * Retrieve next value
     *
     * @return string
     * @since 2.0.0
     */
    public function getNextValue()
    {
        $this->connection->insert($this->meta->getSequenceTable(), []);
        $this->lastIncrementId = $this->connection->lastInsertId($this->meta->getSequenceTable());
        return $this->getCurrentValue();
    }

    /**
     * Calculate current value depends on start value
     *
     * @return string
     * @since 2.0.0
     */
    private function calculateCurrentValue()
    {
        return ($this->lastIncrementId - $this->meta->getActiveProfile()->getStartValue())
        * $this->meta->getActiveProfile()->getStep() + $this->meta->getActiveProfile()->getStartValue();
    }
}
