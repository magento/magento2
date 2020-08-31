<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tax Total Row Renderer
 */
namespace Magento\Tax\Block\Checkout;

use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Sales\Model\ConfigInterface;

/**
 * Class for manage tax amount.
 */
class Tax extends \Magento\Checkout\Block\Total\DefaultTotal
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param ConfigInterface $salesConfig
     * @param array $layoutProcessors
     * @param array $data
     * @param CheckoutHelper|null $checkoutHelper
     * @param TaxHelper|null $taxHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        ConfigInterface $salesConfig,
        array $layoutProcessors = [],
        array $data = [],
        ?CheckoutHelper $checkoutHelper = null,
        ?TaxHelper $taxHelper = null
    ) {
        $data['taxHelper'] = $taxHelper ?? ObjectManager::getInstance()->get(TaxHelper::class);
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $salesConfig,
            $layoutProcessors,
            $data,
            $checkoutHelper
        );
    }

    /**
     * @var string
     */
    protected $_template = 'Magento_Tax::checkout/tax.phtml';
}
