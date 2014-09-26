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
namespace Magento\Msrp\Model\Quote\Address;

/**
 * Msrp items total
 */
class Total extends \Magento\Sales\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Msrp\Helper\Data
     */
    protected $msrpData = null;

    /**
     * @param \Magento\Msrp\Helper\Data $msrpData
     */
    public function __construct(\Magento\Msrp\Helper\Data $msrpData)
    {
        $this->msrpData = $msrpData;
    }

    /**
     * Collect information about MSRP price enabled
     *
     * @param  \Magento\Sales\Model\Quote\Address $address
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Quote\Address $address)
    {
        parent::collect($address);

        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        $canApplyMsrp = false;
        foreach ($items as $item) {
            if (!$item->getParentItemId()
                    && $this->msrpData->isShowBeforeOrderConfirm($item->getProductId())
                    && $this->msrpData->isMinimalPriceLessMsrp($item->getProductId())
            ) {
                $canApplyMsrp = true;
                break;
            }
        }

        $address->setCanApplyMsrp($canApplyMsrp);

        return $this;
    }
}
