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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Multishipping\Model\Payment\Method\Specification;

use Magento\Payment\Model\Method\Specification\AbstractSpecification;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Framework\App\Config\ScopeConfigInterface as StoreConfig;

/**
 * 3D secure specification
 */
class Is3DSecure extends AbstractSpecification
{
    /**
     * Allow multiple address with 3d secure flag
     */
    const FLAG_ALLOW_MULTIPLE_WITH_3DSECURE = 'allow_multiple_with_3dsecure';

    /**#@+
     * 3D Secure card validation store config paths
     */
    const PATH_PAYMENT_3DSECURE = 'payment/%s/enable3ds';

    const PATH_PAYMENT_CENTINEL = 'payment/%s/centinel';

    /**#@-*/

    /**
     * Store config
     *
     * @var StoreConfig
     */
    protected $scopeConfig;

    /**
     * Construct
     *
     * @param PaymentConfig $paymentConfig
     * @param StoreConfig $scopeConfig
     */
    public function __construct(PaymentConfig $paymentConfig, StoreConfig $scopeConfig)
    {
        parent::__construct($paymentConfig);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function isSatisfiedBy($paymentMethod)
    {
        $is3DSecureSupported = isset(
            $this->methodsInfo[$paymentMethod][self::FLAG_ALLOW_MULTIPLE_WITH_3DSECURE]
        ) && $this->methodsInfo[$paymentMethod][self::FLAG_ALLOW_MULTIPLE_WITH_3DSECURE];
        return $is3DSecureSupported || !$this->is3DSecureEnabled($paymentMethod);
    }

    /**
     * Is 3DSecure enabled for payment method
     *
     * @param string $paymentMethod
     * @return bool
     */
    protected function is3DSecureEnabled($paymentMethod)
    {
        return $this->scopeConfig->isSetFlag(
            sprintf(self::PATH_PAYMENT_3DSECURE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $paymentMethod)
        ) || $this->scopeConfig->isSetFlag(
            sprintf(self::PATH_PAYMENT_CENTINEL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $paymentMethod)
        );
    }
}
