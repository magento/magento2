<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Data\Argument\Interpreter;

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Interpreter of string data type.
 * @since 2.2.0
 */
class BaseStringUtils implements InterpreterInterface
{
    /**
     * @var BooleanUtils
     * @since 2.2.0
     */
    private $booleanUtils;

    /**
     * BaseStringUtils constructor.
     *
     * @param BooleanUtils $booleanUtils
     * @since 2.2.0
     */
    public function __construct(BooleanUtils $booleanUtils)
    {
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * {@inheritdoc}
     * @return string
     * @throws \InvalidArgumentException
     * @since 2.2.0
     */
    public function evaluate(array $data)
    {
        if (isset($data['value'])) {
            $result = $data['value'];
            if (!is_string($result)) {
                throw new \InvalidArgumentException('String value is expected.');
            }
        } else {
            $result = '';
        }

        return $result;
    }
}
