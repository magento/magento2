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
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


namespace Magento\Catalog\Block;

class Product extends \Magento\Core\Block\Template
{
    protected $_finalPrice = array();

    /**
     * Product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        array $data = array()
    ) {
        $this->_productFactory = $productFactory;
        parent::__construct($coreData, $context, $data);
    }

    public function getProduct()
    {
        if (!$this->getData('product') instanceof \Magento\Catalog\Model\Product) {
            if ($this->getData('product')->getProductId()) {
                $productId = $this->getData('product')->getProductId();
            }
            if ($productId) {
                $product = $this->_productFactory->create()->load($productId);
                if ($product) {
                    $this->setProduct($product);
                }
            }
        }
        return $this->getData('product');
    }

    public function getPrice()
    {
        return $this->getProduct()->getPrice();
    }

    public function getFinalPrice()
    {
        if (!isset($this->_finalPrice[$this->getProduct()->getId()])) {
            $this->_finalPrice[$this->getProduct()->getId()] = $this->getProduct()->getFinalPrice();
        }
        return $this->_finalPrice[$this->getProduct()->getId()];
    }

    public function getPriceHtml($product)
    {
        $this->setTemplate('product/price.phtml');
        $this->setProduct($product);
        return $this->toHtml();
    }
}
