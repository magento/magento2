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
namespace Magento\Framework\DB\Logger;

use Magento\Framework\DB\LoggerInterface;
use Magento\Framework\Debug;

abstract class LoggerAbstract implements LoggerInterface
{

    /**
     * @var int
     */
    private $timer;

    /**
     * @var bool
     */
    private $logAllQueries;

    /**
     * @var float
     */
    private $logQueryTime;

    /**
     * @var bool
     */
    private $logCallStack;

    /**
     * @param bool $logAllQueries
     * @param float $logQueryTime
     * @param bool $logCallStack
     */
    public function __construct($logAllQueries = false, $logQueryTime = 0.05, $logCallStack = false)
    {
        $this->logAllQueries = $logAllQueries;
        $this->logQueryTime = $logQueryTime;
        $this->logCallStack = $logCallStack;
    }

    /**
     * {@inheritdoc}
     */
    public function startTimer()
    {
        $this->timer = microtime(true);
    }

    /**
     * Get formatted statistics message
     *
     * @param string $type Type of query
     * @param string $sql
     * @param array $bind
     * @param \Zend_Db_Statement_Pdo|null $result
     * @return string
     * @throws \Zend_Db_Statement_Exception
     */
    public function getStats($type, $sql, $bind = [], $result = null)
    {
        $message = '## ' . getmypid() . ' ## ';
        $nl   = "\n";
        $time = sprintf('%.4f', microtime(true) - $this->timer);

        if (!$this->logAllQueries && $time < $this->logQueryTime) {
            return '';
        }
        switch ($type) {
            case self::TYPE_CONNECT:
                $message .= 'CONNECT' . $nl;
                break;
            case self::TYPE_TRANSACTION:
                $message .= 'TRANSACTION ' . $sql . $nl;
                break;
            case self::TYPE_QUERY:
                $message .= 'QUERY' . $nl;
                $message .= 'SQL: ' . $sql . $nl;
                if ($bind) {
                    $message .= 'BIND: ' . var_export($bind, true) . $nl;
                }
                if ($result instanceof \Zend_Db_Statement_Pdo) {
                    $message .= 'AFF: ' . $result->rowCount() . $nl;
                }
                break;
        }
        $message .= 'TIME: ' . $time . $nl;

        if ($this->logCallStack) {
            $message .= 'TRACE: ' . Debug::backtrace(true, false) . $nl;
        }

        $message .= $nl;

        return $message;
    }
}
