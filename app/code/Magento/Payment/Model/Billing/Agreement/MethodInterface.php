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
 * @category    Magento
 * @package     Magento_Payment
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Interface for payment methods that support billing agreements management
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Payment\Model\Billing\Agreement;

interface MethodInterface
{
    /**
     * Init billing agreement
     *
     * @param \Magento\Payment\Model\Billing\AbstractAgreement $agreement
     */
    public function initBillingAgreementToken(\Magento\Payment\Model\Billing\AbstractAgreement $agreement);

    /**
     * Retrieve billing agreement details
     *
     * @param \Magento\Payment\Model\Billing\AbstractAgreement $agreement
     */
    public function getBillingAgreementTokenInfo(\Magento\Payment\Model\Billing\AbstractAgreement $agreement);

    /**
     * Create billing agreement
     *
     * @param \Magento\Payment\Model\Billing\AbstractAgreement $agreement
     */
    public function placeBillingAgreement(\Magento\Payment\Model\Billing\AbstractAgreement $agreement);

    /**
     * Update billing agreement status
     *
     * @param \Magento\Payment\Model\Billing\AbstractAgreement $agreement
     */
    public function updateBillingAgreementStatus(\Magento\Payment\Model\Billing\AbstractAgreement $agreement);
}
