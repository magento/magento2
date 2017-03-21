<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search;

class AbstractKeyValuePair
{
    /**
     * Field name
     *
     * @var string
     */
    protected $name;

    /**
     * Field values
     *
     * @var mixed
     */
    protected $value;

    /**
     * @param string $name
     * @param mixed $value
     * @codeCoverageIgnore
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
     */
    public function getValue()
    {
        return $this->value;
    }
}
