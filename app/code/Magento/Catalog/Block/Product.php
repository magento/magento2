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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Block;

use Magento\Catalog\Model\Product as ModelProduct;

class Product extends \Magento\View\Element\Template
{
    /**
     * @var array
     */
    protected $_finalPrice = array();

    /**
     * Product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = array()
    ) {
        $this->_productFactory = $productFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return ModelProduct
     */
    public function getProduct()
    {
        if (!$this->getData('product') instanceof ModelProduct) {
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

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->getProduct()->getPrice();
    }

    /**
     * @return float
     */
    public function getFinalPrice()
    {
        if (!isset($this->_finalPrice[$this->getProduct()->getId()])) {
            $this->_finalPrice[$this->getProduct()->getId()] = $this->getProduct()->getFinalPrice();
        }
        return $this->_finalPrice[$this->getProduct()->getId()];
    }

    /**
     * @param ModelProduct $product
     * @return string
     */
    public function getPriceHtml($product)
    {
        $this->setTemplate('product/price.phtml');
        $this->setProduct($product);
        return $this->toHtml();
    }
}
