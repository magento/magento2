<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Argument\Interpreter;

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Class \Magento\Framework\Data\Argument\Interpreter\DataObject
 *
 * @since 2.0.0
 */
class DataObject implements InterpreterInterface
{
    /**
     * @var \Magento\Framework\Stdlib\BooleanUtils
     * @since 2.0.0
     */
    protected $booleanUtils;

    /**
     * @param BooleanUtils $booleanUtils
     * @since 2.0.0
     */
    public function __construct(BooleanUtils $booleanUtils)
    {
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * Compute and return effective value of an argument
     *
     * @param array $data
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @since 2.0.0
     */
    public function evaluate(array $data)
    {
        $result = ['instance' => $data['value']];
        if (isset($data['shared'])) {
            $result['shared'] = $this->booleanUtils->toBoolean($data['shared']);
        }
        return $result;
    }
}
