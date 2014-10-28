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
namespace Magento\Paypal\Block\Standard;

/**
 * PayPal Standard payment "form"
 */
class Form extends \Magento\Payment\Block\Form
{
    /**
     * Payment method code
     * @var string
     */
    protected $_methodCode = \Magento\Paypal\Model\Config::METHOD_WPS;

    /**
     * Config model instance
     *
     * @var \Magento\Paypal\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Paypal\Model\ConfigFactory
     */
    protected $_paypalConfigFactory;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Paypal\Model\ConfigFactory $paypalConfigFactory
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Paypal\Model\ConfigFactory $paypalConfigFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = array()
    ) {
        $this->_paypalConfigFactory = $paypalConfigFactory;
        $this->_localeResolver = $localeResolver;
        parent::__construct($context, $data);
    }

    /**
     * Set template and redirect message
     *
     * @return null
     */
    protected function _construct()
    {
        $this->_config = $this->_paypalConfigFactory->create()->setMethod($this->getMethodCode());
        $mark = $this->_getMarkTemplate();
        $mark->setPaymentAcceptanceMarkHref(
            $this->_config->getPaymentMarkWhatIsPaypalUrl($this->_localeResolver)
        )->setPaymentAcceptanceMarkSrc(
            $this->_config->getPaymentMarkImageUrl($this->_localeResolver->getLocaleCode())
        );

        // known issue: code above will render only static mark image
        $this->_initializeRedirectTemplateWithMark($mark);
        return parent::_construct();
    }

    /**
     * Payment method code getter
     *
     * @return string
     */
    public function getMethodCode()
    {
        return $this->_methodCode;
    }

    /**
     * Get initialized mark template
     *
     * @return \Magento\Framework\View\Element\Template
     */
    protected function _getMarkTemplate()
    {
        /** @var $mark \Magento\Framework\View\Element\Template */
        $mark = $this->_layout->createBlock('Magento\Framework\View\Element\Template');
        $mark->setTemplate(
            'Magento_Paypal::payment/mark.phtml'
        );
        return $mark;
    }

    /**
     * Initializes redirect template and set mark
     * @param \Magento\Framework\View\Element\Template $mark
     *
     * @return void
     */
    protected function _initializeRedirectTemplateWithMark(\Magento\Framework\View\Element\Template $mark)
    {
        $this->setTemplate(
            'Magento_Paypal::payment/redirect.phtml'
        )->setRedirectMessage(
            __('You will be redirected to the PayPal website when you place an order.')
        )->setMethodTitle(
            // Output PayPal mark, omit title
            ''
        )->setMethodLabelAfterHtml(
            $mark->toHtml()
        );
    }
}
