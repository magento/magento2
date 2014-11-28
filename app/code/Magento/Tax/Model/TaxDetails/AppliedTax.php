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
namespace Magento\Tax\Model\TaxDetails;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Tax\Api\Data\AppliedTaxInterface;

/**
 * @codeCoverageIgnore
 */
class AppliedTax extends AbstractExtensibleModel implements AppliedTaxInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTaxRateKey()
    {
        return $this->getData(AppliedTaxInterface::KEY_TAX_RATE_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function getPercent()
    {
        return $this->getData(AppliedTaxInterface::KEY_PERCENT);
    }

    /**
     * {@inheritdoc}
     */
    public function getAmount()
    {
        return $this->getData(AppliedTaxInterface::KEY_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getRates()
    {
        return $this->getData(AppliedTaxInterface::KEY_RATES);
    }
}
