<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AbstractAssertCustomerOrderReportResult
 * Check Order report grid for all params
 */
abstract class AbstractAssertCustomerOrderReportResult extends AbstractConstraint
{
    /**
     * Prepare filter
     *
     * @param Customer $customer
     * @param array $columns
     * @param array $report
     * @return array
     */
    public function prepareFilter(Customer $customer, array $columns, array $report)
    {
        $format = '';
        switch ($report['report_period']) {
            case 'Day':
                $format = 'M j, Y';
                break;
            case 'Month':
                $format = 'j/Y';
                break;
            case 'Year':
                $format = 'Y';
                break;
        }

        return [
            'date' => date($format),
            'customer' => $customer->getFirstname() . ' ' . $customer->getLastname(),
            'orders' => $columns['orders'],
            'average' => number_format($columns['average'], 2),
            'total' => number_format($columns['total'], 2)
        ];
    }
}
