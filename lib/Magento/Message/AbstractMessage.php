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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Message;

/**
 * Abstract message model
 */
abstract class AbstractMessage
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var mixed
     */
    protected $class;

    /**
     * @var mixed
     */
    protected $method;

    /**
     * @var mixed
     */
    protected $identifier;

    /**
     * @var bool
     */
    protected $isSticky = false;

    /**
     * @param string $code
     */
    public function __construct($code = '')
    {
        $this->code = $code;
    }

    /**
     * Get message code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get message text
     *
     * @return string
     */
    public function getText()
    {
        return $this->getCode();
    }

    /**
     * Get message type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get message class
     *
     * @param $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * Get message method
     *
     * @param $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Convert message to string
     *
     * @return string
     */
    public function toString()
    {
        $out = $this->getType() . ': ' . $this->getText();
        return $out;
    }

    /**
     * Set message identifier
     *
     * @param string $identifier
     * @return AbstractMessage
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Get message identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set message sticky status
     *
     * @param bool $isSticky
     * @return AbstractMessage
     */
    public function setIsSticky($isSticky = true)
    {
        $this->isSticky = $isSticky;
        return $this;
    }

    /**
     * Get whether message is sticky
     *
     * @return bool
     */
    public function getIsSticky()
    {
        return $this->isSticky;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return AbstractMessage
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }
}
