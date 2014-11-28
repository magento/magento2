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
namespace Magento\Framework\DB;

/**
 * DB logger interface
 */
interface LoggerInterface
{
    /**#@+
     * Types of connections to be logged
     */
    const TYPE_CONNECT     = 'connect';
    const TYPE_TRANSACTION = 'transaction';
    const TYPE_QUERY       = 'query';
    /**#@-*/

    /**
     * Adds log record
     *
     * @param string $str
     * @return void
     */
    public function log($str);

    /**
     * @return void
     */
    public function startTimer();

    /**
     * @param string $type
     * @param string $sql
     * @param array $bind
     * @param \Zend_Db_Statement_Pdo|null $result
     * @return void
     */
    public function logStats($type, $sql, $bind = [], $result = null);

    /**
     * @param \Exception $e
     * @return void
     */
    public function logException(\Exception $e);
}
