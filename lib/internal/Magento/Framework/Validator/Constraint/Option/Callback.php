<?php
/**
 * Constraint callback option
 *
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
namespace Magento\Framework\Validator\Constraint\Option;

class Callback implements \Magento\Framework\Validator\Constraint\OptionInterface
{
    /**
     * @var callable
     */
    protected $_callable;

    /**
     * @var array
     */
    protected $_arguments;

    /**
     * @var bool
     */
    protected $_createInstance;

    /**
     * Create callback
     *
     * @param callable $callable
     * @param mixed $arguments
     * @param bool $createInstance If true than $callable[0] will be evaluated to new instance of class when get value
     */
    public function __construct($callable, $arguments = null, $createInstance = false)
    {
        $this->_callable = $callable;
        $this->setArguments($arguments);
        $this->_createInstance = $createInstance;
    }

    /**
     * Set callback arguments
     *
     * @param mixed $arguments
     * @return void
     */
    public function setArguments($arguments = null)
    {
        if (is_array($arguments)) {
            $this->_arguments = $arguments;
        } elseif (null !== $arguments) {
            $this->_arguments = array($arguments);
        } else {
            $this->_arguments = null;
        }
    }

    /**
     * Get callback value
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getValue()
    {
        $callable = $this->_callable;

        if (is_array($callable) && isset($callable[0]) && is_string($callable[0])) {
            if (!class_exists($callable[0])) {
                throw new \InvalidArgumentException(sprintf('Class "%s" was not found', $callable[0]));
            }
            if ($this->_createInstance) {
                $callable[0] = new $callable[0]();
            }
        } elseif ($this->_createInstance) {
            throw new \InvalidArgumentException('Callable expected to be an array with class name as first element');
        }

        if (!is_callable($callable)) {
            throw new \InvalidArgumentException('Callback does not callable');
        }

        if ($this->_arguments) {
            return call_user_func_array($callable, $this->_arguments);
        } else {
            return call_user_func($callable);
        }
    }
}
