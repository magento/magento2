<?php
/**
 * No such entity service exception
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
namespace Magento\Exception;

class NoSuchEntityException extends \Magento\Exception\Exception
{
    const NO_SUCH_ENTITY = 0;

    /**
     * @param string $fieldName name of the field searched upon
     * @param mixed  $value     the value of the field
     */
    public function __construct($fieldName, $value)
    {
        $message = "No such entity with $fieldName = $value";
        $this->_params[$fieldName] = $value;
        parent::__construct($message, self::NO_SUCH_ENTITY);
    }

    /**
     * @param string $fieldName name of the field searched upon
     * @param mixed  $value     the value of the field
     * @return $this
     */
    public function addField($fieldName, $value)
    {
        $this->message .= "\n $fieldName = $value";
        $this->_params[$fieldName] = $value;
        return $this;
    }
}
