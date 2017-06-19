<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Fixture\Source;

use Magento\Framework\App\ObjectManager;
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
                $timezone = ObjectManager::getInstance()
                    ->get(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
                $date = new \DateTime();
                $date->setTimestamp($timestamp);
                $date->setTimezone(new \DateTimeZone($timezone->getConfigTimezone()));
                $date = $date->format(str_replace($delta, '', $data['pattern']));
            } else {
                $date = date(str_replace($delta, '', $data['pattern']), $timestamp);
            }
            if (!$date) {
                $date = date('m/d/Y');
            }
            $this->data = $date;
        } else {
            $this->data = $data;
        }
    }
}
