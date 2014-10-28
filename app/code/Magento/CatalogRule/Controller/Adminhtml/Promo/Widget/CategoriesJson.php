<?php
/**
 *
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
namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Widget;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Category;
use Magento\Framework\Registry;

class CategoriesJson extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Widget
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     */
    public function __construct(Context $context, Registry $coreRegistry)
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Initialize category object in registry
     *
     * @return Category
     */
    protected function _initCategory()
    {
        $categoryId = (int)$this->getRequest()->getParam('id', false);
        $storeId = (int)$this->getRequest()->getParam('store');

        $category = $this->_objectManager->create('Magento\Catalog\Model\Category');
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = $this->_objectManager->get(
                    'Magento\Store\Model\StoreManager'
                )->getStore(
                    $storeId
                )->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    $this->_redirect('catalog/*/', array('_current' => true, 'id' => null));
                    return false;
                }
            }
        }

        $this->_coreRegistry->register('category', $category);
        $this->_coreRegistry->register('current_category', $category);

        return $category;
    }

    /**
     * Get tree node (Ajax version)
     *
     * @return void
     */
    public function execute()
    {
        $categoryId = (int)$this->getRequest()->getPost('id');
        if ($categoryId) {
            $this->getRequest()->setParam('id', $categoryId);

            if (!($category = $this->_initCategory())) {
                return;
            }
            $block = $this->_view->getLayout()->createBlock(
                'Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree'
            )->setCategoryIds(
                array($categoryId)
            );
            $this->getResponse()->representJson(
                $block->getTreeJson($category)
            );
        }
    }
}
