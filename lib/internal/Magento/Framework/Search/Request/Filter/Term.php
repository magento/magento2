<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Filter;

use Magento\Framework\Search\AbstractKeyValuePair;
use Magento\Framework\Search\Request\FilterInterface;

/**
 * Term Filter
 * @api
 * @since 100.0.2
 */
class Term extends AbstractKeyValuePair implements FilterInterface
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @param string $name
     * @param string|array $value
     * @param string $field
     * @codeCoverageIgnore
     */
    public function __construct($name, $value, $field)
    {
        parent::__construct($name, $value);
        $this->field = $field;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return FilterInterface::TYPE_TERM;
    }

    /**
     * Get Field
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getField()
    {
        return $this->field;
    }
}
