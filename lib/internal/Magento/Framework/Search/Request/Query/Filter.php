<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Query;

use Magento\Framework\Search\Request\QueryInterface;

/**
 * Term Query
 * @api
 * @since 2.0.0
 */
class Filter implements QueryInterface
{
    /**
     * #@+ Reference Types
     */
    const REFERENCE_QUERY = 'query';

    const REFERENCE_FILTER = 'filter';

    /**#@-*/

    /**
     * @var string
     * @since 2.0.0
     */
    protected $name;

    /**
     * Boost
     *
     * @var int|null
     * @since 2.0.0
     */
    protected $boost;

    /**
     * Reference Type
     *
     * @var string
     * @since 2.0.0
     */
    protected $referenceType;

    /**
     * Reference Name
     *
     * @var string
     * @since 2.0.0
     */
    protected $reference;

    /**
     * @param string $name
     * @param int|null $boost
     * @param string $referenceType
     * @param string $reference
     * @since 2.0.0
     */
    public function __construct($name, $boost, $referenceType, $reference)
    {
        $this->name = $name;
        $this->boost = $boost;
        $this->referenceType = $referenceType;
        $this->reference = $reference;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getType()
    {
        return QueryInterface::TYPE_FILTER;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getBoost()
    {
        return $this->boost;
    }

    /**
     * Get Reference
     *
     * @return mixed
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Get Reference Type
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getReferenceType()
    {
        return $this->referenceType;
    }
}
