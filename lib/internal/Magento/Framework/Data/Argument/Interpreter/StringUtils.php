<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Data\Argument\Interpreter;

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Interpreter of string data type that may optionally perform text translation
 */
class StringUtils implements InterpreterInterface
{
    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * Should string utils translate incoming string status.
     *
     * @var bool
     */
    private $translatable;

    /**
     * StringUtils constructor.
     *
     * @param BooleanUtils $booleanUtils
     * @param bool $translatable
     */
    public function __construct(
        BooleanUtils $booleanUtils,
        $translatable = true
    ) {
        $this->booleanUtils = $booleanUtils;
        $this->translatable = $translatable;
    }

    /**
     * {@inheritdoc}
     * @return string
     * @throws \InvalidArgumentException
     */
    public function evaluate(array $data)
    {
        if (isset($data['value'])) {
            $result = $data['value'];
            if (!is_string($result)) {
                throw new \InvalidArgumentException('String value is expected.');
            }
            if ($this->translatable) {
                $needTranslation = isset($data['translate'])
                    ? $this->booleanUtils->toBoolean($data['translate'])
                    : false;
                if ($needTranslation) {
                    $result = (string)new \Magento\Framework\Phrase($result);
                }
            }
        } else {
            $result = '';
        }

        return $result;
    }
}
