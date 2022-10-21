<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Argument\Interpreter;

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\Stdlib\BooleanUtils;

class DataObject implements InterpreterInterface
{
    /**
     * @var \Magento\Framework\Stdlib\BooleanUtils
     */
    protected $booleanUtils;

    /**
     * @param BooleanUtils $booleanUtils
     */
    public function __construct(BooleanUtils $booleanUtils)
    {
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * Compute and return effective value of an argument
     *
     * @param array $data
     * @return array
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function evaluate(array $data): array
    {
        $result = ['instance' => $data['value']];
        if (array_key_exists('sortOrder', $data)) {
            $result['sortOrder'] = $data['sortOrder'];
        }
        if (isset($data['shared'])) {
            $result['shared'] = $this->booleanUtils->toBoolean($data['shared']);
        }
        return $result;
    }
}
