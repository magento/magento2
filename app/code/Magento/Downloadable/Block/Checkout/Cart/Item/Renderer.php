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
 * @package     Magento_Downloadable
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart downloadable item render block
 *
 * @category    Magento
 * @package     Magento_Downloadable
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Downloadable\Block\Checkout\Cart\Item;

class Renderer extends \Magento\Checkout\Block\Cart\Item\Renderer
{

    /**
     * Downloadable catalog product configuration
     *
     * @var \Magento\Downloadable\Helper\Catalog\Product\Configuration
     */
    protected $_downloadProdConfig = null;

    /**
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfiguration
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Downloadable\Helper\Catalog\Product\Configuration $dwnCtlgProdConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Helper\Product\Configuration $productConfiguration,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        \Magento\Downloadable\Helper\Catalog\Product\Configuration $dwnCtlgProdConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = array()
    ) {
        $this->_downloadProdConfig = $dwnCtlgProdConfig;
        parent::__construct($productConfiguration, $coreData, $context, $checkoutSession, $data);
    }

    /**
     * Retrieves item links options
     *
     * @return array
     */
    public function getLinks()
    {
        return $this->_downloadProdConfig->getLinks($this->getItem());
    }

    /**
     * Return title of links section
     *
     * @return string
     */
    public function getLinksTitle()
    {
        return $this->_downloadProdConfig->getLinksTitle($this->getProduct());
    }
}
