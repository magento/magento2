<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Fixture\Source;

use Magento\Mtf\Fixture\DataSource;

/**
 * Class Date.
 *
 * Data keys:
 *  - pattern (Format a local time/date with delta, e.g. 'm/d/Y -3 days' = current day - 3 days)
 *  - apply_timezone (true if it is needed to apply timezone)
 */
class Date extends DataSource
{
    /**
     * Indicates whether timezone setting is applied or not.
     *
     * @var bool
     */
    private $isTimezoneApplied;

    /**
     * @constructor
     * @param array $params
     * @param array|string $data
     * @throws \Exception
     */
    public function __construct(array $params, $data = [])
    {
        $this->params = $params;
        if (isset($data['pattern']) && $data['pattern'] !== '-') {
            $matches = [];
            $delta = '';
            if (preg_match_all('/(\+|-)\d+.+/', $data['pattern'], $matches)) {
                $delta = $matches[0][0];
            }
            $timestamp = $delta === '' ? time() : strtotime($delta);
            if (!$timestamp) {
                throw new \Exception('Invalid date format for "' . $this->params['attribute_code'] . '" field');
            }
            if (isset($data['apply_timezone']) && $data['apply_timezone'] === true) {
                $date = new \DateTime();
                $date->setTimestamp($timestamp);
                $date->setTimezone(new \DateTimeZone($_ENV['magento_timezone']));
                $date = $date->format(str_replace($delta, '', $data['pattern']));
                $this->isTimezoneApplied = true;
            } else {
                $date = date(str_replace($delta, '', $data['pattern']), $timestamp);
                $this->isTimezoneApplied = false;
            }
            if (!$date) {
                $date = date('m/d/Y');
            }
            $this->data = $date;
        } else {
            $this->data = $data;
        }
    }

    /**
     * Verifies if timezone setting has been already applied.
     *
     * @return bool
     */
    public function isTimezoneApplied()
    {
        return $this->isTimezoneApplied;
    }
}
