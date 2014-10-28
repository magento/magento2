<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Data\Argument\Interpreter;

use Magento\Framework\Data\Argument\InterpreterInterface;

/**
 * Interpreter of array data type that supports arrays of unlimited depth
 */
class ArrayType implements InterpreterInterface
{
    /**
     * Interpreter of individual array item
     *
     * @var InterpreterInterface
     */
    private $itemInterpreter;

    /**
     * @param InterpreterInterface $itemInterpreter
     */
    public function __construct(InterpreterInterface $itemInterpreter)
    {
        $this->itemInterpreter = $itemInterpreter;
    }

    /**
     * {@inheritdoc}
     * @return array
     * @throws \InvalidArgumentException
     */
    public function evaluate(array $data)
    {
        $items = isset($data['item']) ? $data['item'] : array();
        if (!is_array($items)) {
            throw new \InvalidArgumentException('Array items are expected.');
        }
        $result = array();
        foreach ($items as $itemKey => $itemData) {
            $result[$itemKey] = $this->itemInterpreter->evaluate($itemData);
        }
        return $result;
    }
}
