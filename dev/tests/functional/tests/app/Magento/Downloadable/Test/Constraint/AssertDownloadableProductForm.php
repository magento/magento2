<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Downloadable\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductForm;

/**
 * Class AssertDownloadableProductForm
 * Assert that downloadable product data on edit page equals to passed from fixture
 */
class AssertDownloadableProductForm extends AssertProductForm
{
    /**
     * Sort fields for fixture and form data
     *
     * @var array
     */
    protected $sortFields = ['downloadable_links/downloadable/link::sort_order'];
}
