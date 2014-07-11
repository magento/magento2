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

namespace Magento\Catalog\Test\Page\Adminhtml;

use Mtf\Page\BackendPage;

/**
 * Class CatalogProductIndex
 * Products page on the Backend
 */
class CatalogProductIndex extends BackendPage
{
    const MCA = 'catalog/product/index';

    protected $_blocks = [
        'productGrid' => [
            'name' => 'productGrid',
            'class' => 'Magento\Catalog\Test\Block\Adminhtml\Product\Grid',
            'locator' => '#productGrid',
            'strategy' => 'css selector',
        ],
        'messagesBlock' => [
            'name' => 'messagesBlock',
            'class' => 'Magento\Core\Test\Block\Messages',
            'locator' => '#messages',
            'strategy' => 'css selector',
        ],
        'productBlock' => [
            'name' => 'productBlock',
            'class' => 'Magento\Catalog\Test\Block\Adminhtml\Product',
            'locator' => '#add_new_product',
            'strategy' => 'css selector',
        ],
        'accessDeniedBlock' => [
            'name' => 'accessDeniedBlock',
            'class' => 'Magento\Backend\Test\Block\Denied',
            'locator' => '[id="page:main-container"]',
            'strategy' => 'css selector',
        ],
        'FormPageActions' => [
            'name' => 'GridPageActions',
            'class' => 'Magento\Catalog\Test\Block\Adminhtml\Product\FormPageActions',
            'locator' => '#add_new_product',
            'strategy' => 'css selector',
        ],
    ];

    /**
     * @return \Magento\Catalog\Test\Block\Adminhtml\Product\Grid
     */
    public function getProductGrid()
    {
        return $this->getBlockInstance('productGrid');
    }

    /**
     * @return \Magento\Core\Test\Block\Messages
     */
    public function getMessagesBlock()
    {
        return $this->getBlockInstance('messagesBlock');
    }

    /**
     * @return \Magento\Catalog\Test\Block\Adminhtml\Product
     */
    public function getProductBlock()
    {
        return $this->getBlockInstance('productBlock');
    }

    /**
     * @return \Magento\Backend\Test\Block\Denied
     */
    public function getAccessDeniedBlock()
    {
        return $this->getBlockInstance('accessDeniedBlock');
    }
}
