<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Framework\DataObject;

/**
 * Minimal option helper for Compare tests: exposes getCode()/setCode and getValue()/setValue.
 */
class OptionCompareTestHelper extends DataObject
{
    /** @var string|int|null */
    private $code;

    /** @var mixed */
    private $value;

    /**
     * @param string|int|null $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string|int|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
