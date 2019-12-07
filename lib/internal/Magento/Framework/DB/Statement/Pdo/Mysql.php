<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Statement\Pdo;

use Magento\Framework\DB\Statement\Parameter;

/**
 * Mysql DB Statement
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mysql extends \Zend_Db_Statement_Pdo
{

    /**
     * Executes statement with binding values to it. Allows transferring specific options to DB driver.
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

        return $this->tryExecute(function () use ($statement) {
            return $statement->execute();
        });
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
            return $this->tryExecute(function () use ($params) {
                return $params !== null ? $this->_stmt->execute($params) : $this->_stmt->execute();
            });
        }
    }

    /**
     * Executes query and avoid warnings.
     *
     * @param callable $callback
     * @return bool
     * @throws \Zend_Db_Statement_Exception
     */
    private function tryExecute($callback)
    {
        $previousLevel = error_reporting(\E_ERROR); // disable warnings for PDO bugs #63812, #74401
        try {
            return $callback();
        } catch (\PDOException $e) {
            $message = sprintf('%s, query was: %s', $e->getMessage(), $this->_stmt->queryString);
            throw new \Zend_Db_Statement_Exception($message, (int)$e->getCode(), $e);
        } finally {
            error_reporting($previousLevel);
        }
    }
}
