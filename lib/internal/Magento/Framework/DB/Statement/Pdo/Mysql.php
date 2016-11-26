<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Mysql DB Statement
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\DB\Statement\Pdo;

use Magento\Framework\DB\Statement\Parameter;

// @codingStandardsIgnoreStart
class Mysql extends \Zend_Db_Statement_Pdo
{
    // @codingStandardsIgnoreEnd

    /**
     * Executes statement with binding values to it.
     * Allows transferring specific options to DB driver.
     *
     * @param array $params Array of values to bind to parameter placeholders.
     * @return bool
     * @throws \Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function _executeWithBinding(array $params)
    {
        // Check whether we deal with named bind
        $isPositionalBind = true;
        foreach ($params as $k => $v) {
            if (!is_int($k)) {
                $isPositionalBind = false;
                break;
            }
        }

        /* @var $statement \PDOStatement */
        $statement = $this->_stmt;
        $bindValues = [];
        // Separate array with values, as they are bound by reference
        foreach ($params as $name => $param) {
            $dataType = \PDO::PARAM_STR;
            $length = null;
            $driverOptions = null;

            if ($param instanceof Parameter) {
                if ($param->getIsBlob()) {
                    // Nothing to do there - default options are fine for MySQL driver
                } else {
                    $dataType = $param->getDataType();
                    $length = $param->getLength();
                    $driverOptions = $param->getDriverOptions();
                }
                $bindValues[$name] = $param->getValue();
            } else {
                $bindValues[$name] = $param;
            }

            $paramName = $isPositionalBind ? $name + 1 : $name;
            $statement->bindParam($paramName, $bindValues[$name], $dataType, $length, $driverOptions);
        }

        try {
            return $statement->execute();
        } catch (\PDOException $e) {
            throw new \Zend_Db_Statement_Exception($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Executes a prepared statement.
     *
     * @param array $params OPTIONAL Values to bind to parameter placeholders.
     * @return bool
     * @throws \Zend_Db_Statement_Exception
     */
    public function _execute(array $params = null)
    {
        $specialExecute = false;
        if ($params) {
            foreach ($params as $param) {
                if ($param instanceof Parameter) {
                    $specialExecute = true;
                    break;
                }
            }
        }

        if ($specialExecute) {
            return $this->_executeWithBinding($params);
        } else {
            return parent::_execute($params);
        }
    }
}
