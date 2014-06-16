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

namespace Magento\Checkout\Test\Block\Cart;

use Mtf\Block\Form;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Class DiscountCodes
 * Discount codes block
 */
class DiscountCodes extends Form
{
    /**
     * Form wrapper selector
     *
     * @var string
     */
    protected $formWrapper = '.content';

    /**
     * Open discount codes form selector
     *
     * @var string
     */
    protected $openForm = '.title';

    /**
     * Fill discount code input selector
     *
     * @var string
     */
    protected $couponCode = '#coupon_code';

    /**
     * Click apply button selector
     *
     * @var string
     */
    protected $applyButton = '.action.apply';

    /**
     * Enter discount code and click apply button
     *
     * @param string $code
     * @return void
     */
    public function applyCouponCode($code)
    {
        if (!$this->_rootElement->find($this->formWrapper)->isVisible()) {
            $this->_rootElement->find($this->openForm, Locator::SELECTOR_CSS)->click();
        }
        $this->_rootElement->find($this->couponCode, Locator::SELECTOR_CSS)->setValue($code);
        $this->_rootElement->find($this->applyButton, Locator::SELECTOR_CSS)->click();
    }
}
