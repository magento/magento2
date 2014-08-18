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
namespace Magento\Tax\Block\Item\Price;

use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Framework\View\Element\Template\Context;

/**
 * Item price render block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Renderer extends \Magento\Checkout\Block\Item\Price\Renderer
{
    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param TaxHelper $taxHelper
     * @param array $data
     */
    public function __construct(Context $context, TaxHelper $taxHelper, array $data = array())
    {
        $this->taxHelper = $taxHelper;
        parent::__construct($context, $data);
    }

    /**
     * Return whether display setting is to display price including tax
     *
     * @return bool
     */
    public function displayPriceInclTax()
    {
        return $this->taxHelper->displayCartPriceInclTax();
    }

    /**
     * Return whether display setting is to display price excluding tax
     *
     * @return bool
     */
    public function displayPriceExclTax()
    {
        return $this->taxHelper->displayCartPriceExclTax();
    }

    /**
     * Return whether display setting is to display both price including tax and price excluding tax
     *
     * @return bool
     */
    public function displayBothPrices()
    {
        return $this->taxHelper->displayCartBothPrices();
    }
}
