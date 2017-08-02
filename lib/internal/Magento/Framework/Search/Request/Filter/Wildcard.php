<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Filter;

use Magento\Framework\Search\AbstractKeyValuePair;
use Magento\Framework\Search\Request\FilterInterface;

/**
 * Wildcard Filter
 * @api
 * @since 2.0.0
 */
class Wildcard extends AbstractKeyValuePair implements FilterInterface
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $field;

    /**
     * @param string $name
     * @param string|array $value
     * @param string $field
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct($name, $value, $field)
    {
        parent::__construct($name, $value);
        $this->field = $field;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getType()
    {
        return FilterInterface::TYPE_WILDCARD;
    }

    /**
     * Get Field
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getField()
    {
        return $this->field;
    }
}
