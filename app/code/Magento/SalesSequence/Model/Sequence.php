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
    const DEFAULT_PATTERN  = "%s%'.09d%s";

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
        string $pattern = self::DEFAULT_PATTERN
    ) {
        $this->meta = $meta;
        $this->connection = $resource->getConnection('sales');
        $this->pattern = $pattern;
    }

    /**
     * Retrieve current value
     *
     * @return ?string
     */
    public function getCurrentValue(): ?string
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
     * @inheritdoc
     */
    public function getNextValue(): string
    {
        $this->connection->insert($this->meta->getSequenceTable(), []);
        $this->lastIncrementId = $this->connection->lastInsertId($this->meta->getSequenceTable());
        return $this->getCurrentValue();
    }

    /**
     * Calculate current value depends on start value
     *
     * @return string
     */
    public function calculateCurrentValue(): string
    {
        return ($this->lastIncrementId - $this->meta->getActiveProfile()->getStartValue())
        * $this->meta->getActiveProfile()->getStep() + $this->meta->getActiveProfile()->getStartValue();
    }

    /**
     * Retrieve the current entity type
     *
     * @return string
     */
    public function getEntityType(): string
    {
        return $this->meta->getEntityType();
    }

    /**
     * Retrieve the current store id
     *
     * @return int
     */
    public function getStoreId(): int
    {
        return $this->meta->getStoreId();
    }

    /**
     * Retrieve the currently active meta profile
     *
     * @return Profile
     */
    public function getMetaProfile(): Profile
    {
        return $this->meta->getActiveProfile();
    }
}
