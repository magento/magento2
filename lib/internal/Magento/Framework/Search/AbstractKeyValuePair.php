<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search;

/**
 * Class \Magento\Framework\Search\AbstractKeyValuePair
 *
 * @since 2.0.0
 */
class AbstractKeyValuePair
{
    /**
     * Field name
     *
     * @var string
     * @since 2.0.0
     */
    protected $name;

    /**
     * Field values
     *
     * @var mixed
     * @since 2.0.0
     */
    protected $value;

    /**
     * @param string $name
     * @param mixed $value
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Get field name
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get field values
     *
     * @return mixed Return data in raw-formt. Must be escaped for using in sql
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getValue()
    {
        return $this->value;
    }
}
