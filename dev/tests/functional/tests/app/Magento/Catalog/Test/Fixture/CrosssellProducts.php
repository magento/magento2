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

namespace Magento\Catalog\Test\Fixture;

use Mtf\Factory\Factory;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Crosssell;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Related;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Upsell;

class CrosssellProducts extends AssignProducts
{
    /**
     * @var string
     */
    protected $assignType = 'crosssell';

    /**
     * @var array
     */
    protected $_products = array();

    /**
     * Init Data
     */
    protected function _initData()
    {
        $this->_dataConfig = array(
            'assignType ' => $this->assignType,
        );
        /** @var  $type Related|Upsell */
        $type = 'Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\\' . ucfirst(strtolower($this->assignType));
        $productsArray = array();
        foreach ($this->_products as $key => $product) {
            /** @var $product \Magento\Catalog\Test\Fixture\Product */
            $productsArray['product_' . $key] = array(
                'sku' => $product->getProductSku(),
                'name' => $product->getName()
            );
        }
        $this->_data['fields'][$this->assignType . '_products']['value'] = $productsArray;
        $this->_data['fields'][$this->assignType . '_products']['group'] = $type::GROUP;

        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoCatalogAssignProducts($this->_dataConfig, $this->_data);
    }

    /**
     * Set specified product to local data
     *
     * @param array $products
     * @return $this
     */
    public function setProducts(array $products)
    {
        $this->_products = $products;
        $this->_initData();
        return $this;
    }
}
