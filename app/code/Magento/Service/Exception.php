<?php
/**
 * Generic exception for usage in services implementation
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
namespace Magento\Service;

class Exception extends \Magento\Model\Exception
{
    /** @var array */
    protected $_parameters;

    /**
     * Exception name.
     *
     * @var string
     */
    protected $_name;

    /**
     * {@inheritdoc}
     * @param array $parameters - Array of optional exception parameters.
     */
    public function __construct(
        $message = "",
        $code = 0,
        \Exception $previous = null,
        array $parameters = array(),
        $name = ''
    ) {
        parent::__construct($message, $code, $previous);
        $this->_parameters = $parameters;
        $this->_name = $name;
    }

    /**
     * Return the optional list of parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->_parameters;
    }

    /**
     * Retrieve exception name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
}
