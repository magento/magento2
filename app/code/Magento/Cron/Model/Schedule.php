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

namespace Magento\Cron\Model;

use Magento\Cron\Exception;

/**
 * Crontab schedule model
 *
 * @method \Magento\Cron\Model\Resource\Schedule _getResource()
 * @method \Magento\Cron\Model\Resource\Schedule getResource()
 * @method string getJobCode()
 * @method \Magento\Cron\Model\Schedule setJobCode(string $value)
 * @method string getStatus()
 * @method \Magento\Cron\Model\Schedule setStatus(string $value)
 * @method string getMessages()
 * @method \Magento\Cron\Model\Schedule setMessages(string $value)
 * @method string getCreatedAt()
 * @method \Magento\Cron\Model\Schedule setCreatedAt(string $value)
 * @method string getScheduledAt()
 * @method \Magento\Cron\Model\Schedule setScheduledAt(string $value)
 * @method string getExecutedAt()
 * @method \Magento\Cron\Model\Schedule setExecutedAt(string $value)
 * @method string getFinishedAt()
 * @method \Magento\Cron\Model\Schedule setFinishedAt(string $value)
 * @method array getCronExprArr()
 * @method \Magento\Cron\Model\Schedule setCronExprArr(array $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Schedule extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_PENDING = 'pending';

    const STATUS_RUNNING = 'running';

    const STATUS_SUCCESS = 'success';

    const STATUS_MISSED = 'missed';

    const STATUS_ERROR = 'error';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_date = $date;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('Magento\Cron\Model\Resource\Schedule');
    }

    /**
     * @param string $expr
     * @return $this
     * @throws Exception
     */
    public function setCronExpr($expr)
    {
        $e = preg_split('#\s+#', $expr, null, PREG_SPLIT_NO_EMPTY);
        if (sizeof($e) < 5 || sizeof($e) > 6) {
            throw new Exception('Invalid cron expression: ' . $expr);
        }

        $this->setCronExprArr($e);
        return $this;
    }

    /**
     * Checks the observer's cron expression against time
     *
     * Supports $this->setCronExpr('* 0-5,10-59/5 2-10,15-25 january-june/2 mon-fri')
     *
     * @return bool
     */
    public function trySchedule()
    {
        $time = $this->getScheduledAt();
        $e = $this->getCronExprArr();

        if (!$e || !$time) {
            return false;
        }
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }

        $d = getdate($this->_date->timestamp($time));

        $match = $this->matchCronExpression($e[0], $d['minutes'])
            && $this->matchCronExpression($e[1], $d['hours'])
            && $this->matchCronExpression($e[2], $d['mday'])
            && $this->matchCronExpression($e[3], $d['mon'])
            && $this->matchCronExpression($e[4], $d['wday']);

        return $match;
    }

    /**
     * @param string $expr
     * @param int $num
     * @return bool
     * @throws Exception
     */
    public function matchCronExpression($expr, $num)
    {
        // handle ALL match
        if ($expr === '*') {
            return true;
        }

        // handle multiple options
        if (strpos($expr, ',') !== false) {
            foreach (explode(',', $expr) as $e) {
                if ($this->matchCronExpression($e, $num)) {
                    return true;
                }
            }
            return false;
        }

        // handle modulus
        if (strpos($expr, '/') !== false) {
            $e = explode('/', $expr);
            if (sizeof($e) !== 2) {
                throw new Exception("Invalid cron expression, expecting 'match/modulus': " . $expr);
            }
            if (!is_numeric($e[1])) {
                throw new Exception("Invalid cron expression, expecting numeric modulus: " . $expr);
            }
            $expr = $e[0];
            $mod = $e[1];
        } else {
            $mod = 1;
        }

        // handle all match by modulus
        if ($expr === '*') {
            $from = 0;
            $to = 60;
        } elseif (strpos($expr, '-') !== false) {
            // handle range
            $e = explode('-', $expr);
            if (sizeof($e) !== 2) {
                throw new Exception("Invalid cron expression, expecting 'from-to' structure: " . $expr);
            }

            $from = $this->getNumeric($e[0]);
            $to = $this->getNumeric($e[1]);
        } else {
            // handle regular token
            $from = $this->getNumeric($expr);
            $to = $from;
        }

        if ($from === false || $to === false) {
            throw new Exception("Invalid cron expression: " . $expr);
        }

        return $num >= $from && $num <= $to && $num % $mod === 0;
    }

    /**
     * @param int|string $value
     * @return bool|int|string
     */
    public function getNumeric($value)
    {
        static $data = array(
            'jan' => 1,
            'feb' => 2,
            'mar' => 3,
            'apr' => 4,
            'may' => 5,
            'jun' => 6,
            'jul' => 7,
            'aug' => 8,
            'sep' => 9,
            'oct' => 10,
            'nov' => 11,
            'dec' => 12,
            'sun' => 0,
            'mon' => 1,
            'tue' => 2,
            'wed' => 3,
            'thu' => 4,
            'fri' => 5,
            'sat' => 6
        );

        if (is_numeric($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = strtolower(substr($value, 0, 3));
            if (isset($data[$value])) {
                return $data[$value];
            }
        }

        return false;
    }

    /**
     * Sets a job to STATUS_RUNNING only if it is currently in STATUS_PENDING.
     * Returns true if status was changed and false otherwise.
     *
     * This is used to implement locking for cron jobs.
     *
     * @return boolean
     */
    public function tryLockJob()
    {
        if ($this->_getResource()->trySetJobStatusAtomic(
            $this->getId(),
            self::STATUS_RUNNING,
            self::STATUS_PENDING
        )) {
            $this->setStatus(self::STATUS_RUNNING);
            return true;
        }
        return false;
    }
}
