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
namespace Magento\Framework\View\Layout\Argument\Interpreter;

use Magento\Framework\Data\Argument\InterpreterInterface;

/**
 * Interpreter of named parameters
 */
class NamedParams implements InterpreterInterface
{
    /**
     * Interpreter of individual parameter
     *
     * @var InterpreterInterface
     */
    private $paramInterpreter;

    /**
     * @param InterpreterInterface $paramInterpreter
     */
    public function __construct(InterpreterInterface $paramInterpreter)
    {
        $this->paramInterpreter = $paramInterpreter;
    }

    /**
     * {@inheritdoc}
     * @return array
     * @throws \InvalidArgumentException
     */
    public function evaluate(array $data)
    {
        $params = isset($data['param']) ? $data['param'] : array();
        if (!is_array($params)) {
            throw new \InvalidArgumentException('Layout argument parameters are expected to be an array.');
        }
        $result = array();
        foreach ($params as $paramKey => $paramData) {
            if (!is_array($paramData)) {
                throw new \InvalidArgumentException('Parameter data of layout argument is expected to be an array.');
            }
            $result[$paramKey] = $this->paramInterpreter->evaluate($paramData);
        }
        return $result;
    }
}
