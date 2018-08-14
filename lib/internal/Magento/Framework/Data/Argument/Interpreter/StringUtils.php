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
 */
class StringUtils implements InterpreterInterface
{
    /**
     * @var BaseStringUtils
     */
    private $baseStringUtils;

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * StringUtils constructor.
     *
     * @param BooleanUtils $booleanUtils
     * @param BaseStringUtils $baseStringUtils
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
