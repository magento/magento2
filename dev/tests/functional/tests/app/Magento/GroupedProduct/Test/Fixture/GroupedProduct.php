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

namespace Magento\GroupedProduct\Test\Fixture;

use Mtf\System\Config;
use Mtf\Factory\Factory;
use Magento\Catalog\Test\Fixture\Product;

/**
 * Class GroupedProduct
 * Grouped product data
 *
 */
class GroupedProduct extends Product
{
    const GROUP = 'grouped-product';

    /**
     * List of fixtures from created products
     *
     * @var array
     */
    protected $products = array();

    /**
     * Custom constructor to create Grouped product
     *
     * @param Config $configuration
     * @param array $placeholders
     */
    public function __construct(Config $configuration, $placeholders = array())
    {
        parent::__construct($configuration, $placeholders);

        $this->_placeholders['simple::getName'] = array($this, 'productProvider');
        $this->_placeholders['virtual::getName'] = array($this, 'productProvider');
        $this->_placeholders['downloadable::getName'] = array($this, 'productProvider');
        $this->_placeholders['simple::getProductId'] = array($this, 'productProvider');
        $this->_placeholders['virtual::getProductId'] = array($this, 'productProvider');
        $this->_placeholders['downloadable::getProductId'] = array($this, 'productProvider');
    }

    /**
     * Get Associated Products
     *
     * @return array
     */
    public function getAssociatedProducts()
    {
        return $this->products;
    }

    /**
     * Get Associated Product Names
     *
     * @return array
     */
    public function getAssociatedProductNames()
    {
        $names = array();
        foreach ($this->getData('fields/grouped_products/value') as $product) {
            $names[] = $product['search_data']['name'];
        }
        return $names;
    }

    /**
     * Init Data
     */
    protected function _initData()
    {
        $this->_dataConfig = array(
            'type_id' => 'grouped',
            'constraint' => 'Success',
            'create_url_params' => array(
                'type' => 'grouped',
                'set' => static::DEFAULT_ATTRIBUTE_SET_ID,
            ),
        );
        $this->_data = array(
            'fields' => array(
                'name' => array(
                    'value' => 'Grouped Product %isolation%',
                    'group' => static::GROUP_PRODUCT_DETAILS
                ),
                'sku' => array(
                    'value' => 'grouped_sku_%isolation%',
                    'group' => static::GROUP_PRODUCT_DETAILS
                ),
                'grouped_products' => array(
                    'value' => array(
                        'assigned_product_0' => array(
                            'search_data' => array(
                                'name' => '%simple::getName%',
                            ),
                            'data' => array(
                                'selection_qty' => array(
                                    'value' => 1
                                ),
                                'product_id' => array(
                                    'value' => '%simple::getProductId%'
                                )
                            )
                        ),
                        'assigned_product_1' => array(
                            'search_data' => array(
                                'name' => '%virtual::getName%',
                            ),
                            'data' => array(
                                'selection_qty' => array(
                                    'value' => 1
                                ),
                                'product_id' => array(
                                    'value' => '%virtual::getProductId%'
                                )
                            )
                        ),
                        'assigned_product_2' => array(
                            'search_data' => array(
                                'name' => '%downloadable::getName%',
                            ),
                            'data' => array(
                                'selection_qty' => array(
                                    'value' => 1
                                ),
                                'product_id' => array(
                                    'value' => '%downloadable::getProductId%'
                                )
                            )
                        )
                    ),
                    'group' => static::GROUP
                )
            ),
        );

        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoGroupedProductGroupedProduct($this->_dataConfig, $this->_data);
    }
}
