<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Block\Adminhtml\Customer\Counts;

use Magento\Reports\Test\Block\Adminhtml\AbstractFilter;

/**
 * Class Filter
 * Filter for Order count Report
 */
class Filter extends AbstractFilter
{
    /**
     * Date fields
     *
     * @var array
     */
    protected $dateFields = ['report_from', 'report_to'];
}
