<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Test\Block\Adminhtml;

use Magento\Backend\Test\Block\Widget\Grid;

/**
 * Class AgreementGrid
 * Backend Agreement grid
 */
class AgreementGrid extends Grid
{
    /**
     * Locator value for link in action column
     *
     * @var string
     */
    protected $editLink = 'td[class*=col-name]';

    /**
     * First row selector
     *
     * @var string
     */
    protected $firstRowSelector = '//tbody/tr[./td[contains(@class, "col-name")]][1]';

    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'name' => [
            'selector' => 'input[name="name"]',
        ],
    ];
}
