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

namespace Magento\CatalogSearch\Test\Page;

/**
 * Class AdvancedResult
 */
class AdvancedResult extends CatalogsearchResult
{
    const MCA = 'catalogsearch/advanced/result';

    /**
     * Custom constructor
     *
     * @return void
     */
    protected function _init()
    {
        $this->_blocks['searchResultBlock'] = [
            'name' => 'searchResultBlock',
            'class' => 'Magento\CatalogSearch\Test\Block\Advanced\Result',
            'locator' => '.column.main',
            'strategy' => 'css selector',
        ];
        $this->_blocks['toolbar'] = [
            'name' => 'toolbar',
            'class' => 'Magento\Catalog\Test\Block\Product\ProductList\Toolbar',
            'locator' => '.column.main',
            'strategy' => 'css selector',
        ];
        parent::_init();
    }

    /**
     * @return \Magento\CatalogSearch\Test\Block\Advanced\Result
     */
    public function getSearchResultBlock()
    {
        return $this->getBlockInstance('searchResultBlock');
    }

    /**
     * @return \Magento\Catalog\Test\Block\Product\ProductList\Toolbar
     */
    public function getToolbar()
    {
        return $this->getBlockInstance('toolbar');
    }
}
