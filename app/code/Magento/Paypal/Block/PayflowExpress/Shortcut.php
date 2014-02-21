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

/**
 * Paypal expess checkout shortcut link
 */
namespace Magento\Paypal\Block\PayflowExpress;

class Shortcut extends \Magento\Paypal\Block\Express\Shortcut
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_paymentMethodCode = \Magento\Paypal\Model\Config::METHOD_WPP_PE_EXPRESS;

    /**
     * Start express action
     *
     * @var string
     */
    protected $_startAction = 'paypal/payflowexpress/start';

    /**
     * Express checkout model factory name
     *
     * @var string
     */
    protected $_checkoutType = 'Magento\Paypal\Model\PayflowExpress\Checkout';

    /**
     * Get shortcut alias
     *
     * @return string
     */
    public function getAlias()
    {
        return 'product.info.addtocart.payflow';
    }
}
