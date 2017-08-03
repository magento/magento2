<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Config element model
 */
namespace Magento\Framework\App\Config;

/**
 * @api
 * @since 2.0.0
 */
class Element extends \Magento\Framework\Simplexml\Element
{
    /**
     * Enter description here...
     *
     * @param string $var
     * @param boolean $value
     * @return boolean
     * @SuppressWarnings(PHPMD.ShortMethodName)
     * @since 2.0.0
     */
    public function is($var, $value = true)
    {
        $flag = $this->{$var};

        if ($value === true) {
            $flag = strtolower((string)$flag);
            if (!empty($flag) && 'false' !== $flag && 'off' !== $flag) {
                return true;
            } else {
                return false;
            }
        }

        return !empty($flag) && 0 === strcasecmp($value, (string)$flag);
    }

    /**
     * Enter description here...
     *
     * @return string
     * @since 2.0.0
     */
    public function getClassName()
    {
        if ($this->class) {
            $model = (string)$this->class;
        } elseif ($this->model) {
            $model = (string)$this->model;
        } else {
            return false;
        }
        return $model;
    }
}
