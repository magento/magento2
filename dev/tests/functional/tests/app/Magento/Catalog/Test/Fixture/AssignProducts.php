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

use Mtf\System\Config;
use Mtf\Factory\Factory;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Related;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Upsell;

class AssignProducts extends Product
{
    protected $assignType = '';

    /**
     * {@inheritdoc}
     */
    public function __construct(Config $configuration, $placeholders = array())
    {
        parent::__construct($configuration, $placeholders);

        $this->_placeholders[$this->assignType . '_simple::getProductSku'] = array($this, 'productProvider');
        $this->_placeholders[$this->assignType . '_simple::getName'] = array($this, 'productProvider');
        $this->_placeholders[$this->assignType . '_configurable::getProductSku'] = array($this, 'productProvider');
        $this->_placeholders[$this->assignType . '_configurable::getName'] = array($this, 'productProvider');
    }

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
        $this->_data = array(
            'fields' => array(
                $this->assignType . '_products' => array(
                    'value' => array(
                        'product_1' => array(
                            'sku' => '%' . $this->assignType . '_simple::getProductSku%',
                            'name' => '%' . $this->assignType . '_simple::getName%'
                        ),
                        'product_2' => array(
                            'sku' => '%' . $this->assignType . '_configurable::getProductSku%',
                            'name' => '%' . $this->assignType . '_configurable::getName%'
                        )
                    ),
                    'group' => $type::GROUP
                )
            ),
        );

        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoCatalogAssignProducts($this->_dataConfig, $this->_data);
    }

    /**
     * @param string $productData
     * @return string
     */
    protected function formatProductType($productData)
    {
        return str_replace($this->assignType . '_', '', $productData);
    }
}
