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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Hosted Sole Solution helper
 */
namespace Magento\Paypal\Helper;

class Hss extends \Magento\Core\Helper\AbstractHelper
{
    /**
     * Hosted Sole Solution methods
     *
     * @var array
     */
    protected $_hssMethods = array(
        \Magento\Paypal\Model\Config::METHOD_HOSTEDPRO,
        \Magento\Paypal\Model\Config::METHOD_PAYFLOWLINK,
        \Magento\Paypal\Model\Config::METHOD_PAYFLOWADVANCED
    );

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @param \Magento\Core\Helper\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\View\LayoutInterface $layout
     */
    public function __construct(
        \Magento\Core\Helper\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\View\LayoutInterface $layout
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_layout = $layout;
        parent::__construct($context);
    }

    /**
     * Get template for button in order review page if HSS method was selected
     *
     * @param string $name template name
     * @param string $block buttons block name
     * @return string
     */
    public function getReviewButtonTemplate($name, $block)
    {
        $quote = $this->_checkoutSession->getQuote();
        if ($quote) {
            $payment = $quote->getPayment();
            if ($payment && in_array($payment->getMethod(), $this->_hssMethods)) {
                return $name;
            }
        }

        $blockObject = $this->_layout->getBlock($block);
        if ($blockObject) {
            return $blockObject->getTemplate();
        }

        return '';
    }

    /**
     * Get methods
     *
     * @return array
     */
    public function getHssMethods()
    {
        return $this->_hssMethods;
    }
}
