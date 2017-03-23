<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Event cron observer object
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Event\Observer;

class Cron extends \Magento\Framework\Event\Observer
{
    /**
     * Checks the observer's cron string against event's name
     *
     * Supports $this->setCronExpr('* 0-5,10-59/5 2-10,15-25 january-june/2 mon-fri')
     *
     * @param \Magento\Framework\Event $event
     * @return boolean
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isValidFor(\Magento\Framework\Event $event)
    {
        $e = preg_split('#\s+#', $this->getCronExpr(), null, PREG_SPLIT_NO_EMPTY);
        if (sizeof($e) !== 5) {
            return false;
        }

        $d = getdate($this->getNow());

        return $this->matchCronExpression(
            $e[0],
            $d['minutes']
        ) && $this->matchCronExpression(
            $e[1],
            $d['hours']
        ) && $this->matchCronExpression(
            $e[2],
            $d['mday']
        ) && $this->matchCronExpression(
            $e[3],
            $d['mon']
        ) && $this->matchCronExpression(
            $e[4],
            $d['wday']
        );
    }

    /**
     * @return int
     */
    public function getNow()
    {
        if (!$this->hasNow()) {
            $this->setNow(time());
        }
        return $this->getData('now');
    }

    /**
     * @param string $expr
     * @param int $num
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
                return false;
            }
            $expr = $e[0];
            $mod = $e[1];
            if (!is_numeric($mod)) {
                return false;
            }
        } else {
            $mod = 1;
        }

        // handle range
        if (strpos($expr, '-') !== false) {
            $e = explode('-', $expr);
            if (sizeof($e) !== 2) {
                return false;
            }

            $from = $this->getNumeric($e[0]);
            $to = $this->getNumeric($e[1]);

            return $from !== false && $to !== false && $num >= $from && $num <= $to && $num % $mod === 0;
        }

        // handle regular token
        $value = $this->getNumeric($expr);
        return $value !== false && $num == $value && $num % $mod === 0;
    }

    /**
     * @param int|string $value
     * @return bool|string
     */
    public function getNumeric($value)
    {
        static $data = [
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
            'sat' => 6,
        ];

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
}
