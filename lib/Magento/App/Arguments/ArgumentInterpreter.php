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
namespace Magento\App\Arguments;

use Magento\Data\Argument\InterpreterInterface;
use Magento\Data\Argument\Interpreter\Constant;
use Magento\Data\Argument\MissingOptionalValueException;
use Magento\App\Arguments;

/**
 * Interpreter that returns value of an application argument, retrieving its name from a constant
 */
class ArgumentInterpreter implements InterpreterInterface
{
    /**
     * @var Arguments
     */
    private $arguments;

    /**
     * @var Constant
     */
    private $constInterpreter;

    /**
     * @param Arguments $arguments
     * @param Constant $constInterpreter
     */
    public function __construct(Arguments $arguments, Constant $constInterpreter)
    {
        $this->arguments = $arguments;
        $this->constInterpreter = $constInterpreter;
    }

    /**
     * {@inheritdoc}
     * @return mixed
     * @throws MissingOptionalValueException
     */
    public function evaluate(array $data)
    {
        $argumentName = $this->constInterpreter->evaluate($data);
        $result = $this->arguments->get($argumentName);
        if ($result === null) {
            throw new MissingOptionalValueException("Value of application argument '{$argumentName}' is not defined.");
        }
        return $result;
    }
}
