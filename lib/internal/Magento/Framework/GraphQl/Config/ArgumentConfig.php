<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config;

use Magento\Framework\GraphQl\Argument\ValueParserInterface;
use Magento\Framework\GraphQl\Argument\ArgumentValueInterface;

/**
 * Data object that holds the configuration for a argument of a field
 */
class ArgumentConfig
{
    /**
     * @var ArgumentValueInterface|int|string|float|bool
     */

    private $defaultValue;

    /**
     * @var ValueParserInterface
     */
    private $valueParser;

    /**
     * @param ArgumentValueInterface|int|string|float|bool|null $defaultValue
     * @param ValueParserInterface|null $valueParser
     */
    public function __construct(
        $defaultValue = null,
        ValueParserInterface $valueParser = null
    ) {
        $this->defaultValue = $defaultValue;
        $this->valueParser = $valueParser;
    }

    /**
     * Return the default value
     *
     * @return ArgumentValueInterface|int|string|float|bool|null $defaultValue
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Return a value parser if one is set
     *
     * @return ValueParserInterface|null
     */
    public function getValueParser()
    {
        return $this->valueParser;
    }
}
