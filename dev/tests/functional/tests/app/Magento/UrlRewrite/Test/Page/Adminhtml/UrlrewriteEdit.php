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

namespace Magento\UrlRewrite\Test\Page\Adminhtml;

use Mtf\Page\BackendPage;

/**
 * Class UrlrewriteEdit
 */
class UrlrewriteEdit extends BackendPage
{
    const MCA = 'admin/urlrewrite/edit';

    protected $_blocks = [
        'treeBlock' => [
            'name' => 'treeBlock',
            'class' => 'Magento\UrlRewrite\Test\Block\Adminhtml\Catalog\Category\Tree',
            'locator' => '[id="page:main-container"]',
            'strategy' => 'css selector',
        ],
        'formBlock' => [
            'name' => 'formBlock',
            'class' => 'Magento\UrlRewrite\Test\Block\Adminhtml\Catalog\Edit\Form',
            'locator' => '#edit_form',
            'strategy' => 'css selector',
        ],
        'messagesBlock' => [
            'name' => 'messagesBlock',
            'class' => 'Magento\Core\Test\Block\Messages',
            'locator' => '#messages .messages',
            'strategy' => 'css selector',
        ],
        'pageMainActions' => [
            'name' => 'pageMainActions',
            'class' => 'Magento\Backend\Test\Block\FormPageActions',
            'locator' => '.page-main-actions',
            'strategy' => 'css selector',
        ],
        'productGridBlock' => [
            'name' => 'productGridBlock',
            'class' => 'Magento\UrlRewrite\Test\Block\Adminhtml\Catalog\Product\Grid',
            'locator' => '[id="productGrid"]',
            'strategy' => 'css selector',
        ],
        'urlRewriteTypeSelectorBlock' => [
            'name' => 'urlRewriteTypeSelectorBlock',
            'class' => 'Magento\UrlRewrite\Test\Block\Adminhtml\Selector',
            'locator' => '[data-ui-id="urlrewrite-type-selector"]',
            'strategy' => 'css selector',
        ],
        'cmsGridBlock' => [
            'name' => 'gridBlock',
            'class' => 'Magento\UrlRewrite\Test\Block\Adminhtml\Cms\Page\Grid',
            'locator' => '#cmsPageGrid',
            'strategy' => 'css selector',
        ],
    ];

    /**
     * @return \Magento\UrlRewrite\Test\Block\Adminhtml\Catalog\Category\Tree
     */
    public function getTreeBlock()
    {
        return $this->getBlockInstance('treeBlock');
    }

    /**
     * @return \Magento\UrlRewrite\Test\Block\Adminhtml\Catalog\Edit\Form
     */
    public function getFormBlock()
    {
        return $this->getBlockInstance('formBlock');
    }

    /**
     * @return \Magento\Core\Test\Block\Messages
     */
    public function getMessagesBlock()
    {
        return $this->getBlockInstance('messagesBlock');
    }

    /**
     * @return \Magento\Backend\Test\Block\FormPageActions
     */
    public function getPageMainActions()
    {
        return $this->getBlockInstance('pageMainActions');
    }

    /**
     * @return \Magento\UrlRewrite\Test\Block\Adminhtml\Catalog\Product\Grid
     */
    public function getProductGridBlock()
    {
        return $this->getBlockInstance('productGridBlock');
    }

    /**
     * @return \Magento\UrlRewrite\Test\Block\Adminhtml\Selector
     */
    public function getUrlRewriteTypeSelectorBlock()
    {
        return $this->getBlockInstance('urlRewriteTypeSelectorBlock');
    }

    /**
     * @return \Magento\UrlRewrite\Test\Block\Adminhtml\Cms\Page\Grid
     */
    public function getCmsGridBlock()
    {
        return $this->getBlockInstance('cmsGridBlock');
    }
}
