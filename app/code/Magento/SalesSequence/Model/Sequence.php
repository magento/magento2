<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\SalesSequence\Model;

use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\DB\Sequence\SequenceInterface;

/**
 * Class Sequence represents sequence in logic
 *
 * @api
 * @since 100.0.2
 */
class Sequence implements SequenceInterface
{
    /**
     * Default pattern for Sequence
     */
    public const DEFAULT_PATTERN  = "%s%'.09d%s";

    /**
     * @var string
     */
    private $lastIncrementId;

    /**
     * @var Meta
     */
    private $meta;

    /**
     * @var false|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var string
     */
    private $pattern;

    /**
     * @param Meta $meta
     * @param AppResource $resource
     * @param string $pattern
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
     * Returns the numeric part of the current value, before prefix, suffix and pattern are applied.
     *
     * @return int|null Null until a value has been generated
     */
    public function calculateCurrentValue(): ?int
    {
        if (!isset($this->lastIncrementId)) {
            return null;
        }

        return ($this->lastIncrementId - $this->meta->getActiveProfile()->getStartValue())
        * $this->meta->getActiveProfile()->getStep() + $this->meta->getActiveProfile()->getStartValue();
    }

    /**
     * Retrieve entity type the sequence generates values for
     *
     * @return string
     */
    public function getEntityType(): string
    {
        return (string)$this->meta->getData('entity_type');
    }

    /**
     * Retrieve store ID the sequence generates values for
     *
     * @return int
     */
    public function getStoreId(): int
    {
        return (int)$this->meta->getData('store_id');
    }

    /**
     * Retrieve active profile holding prefix, suffix, start value and step
     *
     * @return Profile
     */
    public function getMetaProfile(): Profile
    {
        return $this->meta->getActiveProfile();
    }
}
