<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Query;

use Magento\Framework\Search\Request\QueryInterface;

/**
 * Term Query
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
     */
    protected $name;

    /**
     * Boost
     *
     * @var int|null
     */
    protected $boost;

    /**
     * Reference Type
     *
     * @var string
     */
    protected $referenceType;

    /**
     * Reference Name
     *
     * @var string
     */
    protected $reference;

    /**
     * @param string $name
     * @param int|null $boost
     * @param string $referenceType
     * @param string $reference
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
     */
    public function getType()
    {
        return QueryInterface::TYPE_FILTER;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
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
     */
    public function getReferenceType()
    {
        return $this->referenceType;
    }
}
