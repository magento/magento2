<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Data\Argument\Interpreter;

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Interpreter of string data type that may optionally perform text translation.
 * @since 2.0.0
 */
class StringUtils implements InterpreterInterface
{
    /**
     * @var BaseStringUtils
     * @since 2.2.0
     */
    private $baseStringUtils;

    /**
     * @var BooleanUtils
     * @since 2.0.0
     */
    private $booleanUtils;

    /**
     * StringUtils constructor.
     *
     * @param BooleanUtils $booleanUtils
     * @param BaseStringUtils $baseStringUtils
     * @since 2.0.0
     */
    public function __construct(
        BooleanUtils $booleanUtils,
        BaseStringUtils $baseStringUtils
    ) {
        $this->booleanUtils = $booleanUtils;
        $this->baseStringUtils = $baseStringUtils;
    }

    /**
     * {@inheritdoc}
     * @return string
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function evaluate(array $data)
    {
        $result = $this->baseStringUtils->evaluate($data);
        $needTranslation = isset($data['translate'])
            ? $this->booleanUtils->toBoolean($data['translate'])
            : false;
        if ($needTranslation) {
            $result = (string)new \Magento\Framework\Phrase($result);
        }

        return $result;
    }
}
