<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Block\Adminhtml\Rule\Edit;

use Mtf\Block\Form as FormInterface;

/**
 * Class TaxRate
 * Tax rate block
 */
class TaxRate extends FormInterface
{
    /**
     * 'Save' button on dialog window for creating new tax rate
     *
     * @var string
     */
    protected $saveTaxRate = '#tax-rule-edit-apply-button';

    /**
     * Clicking 'Save' button on dialog window for creating new tax rate
     *
     * @return void
     */
    public function saveTaxRate()
    {
        $this->_rootElement->find($this->saveTaxRate)->click();
    }
}
