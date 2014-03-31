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
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Category edit block
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Category\Edit;

use Magento\Backend\Block\Template;

class Form extends \Magento\Catalog\Block\Adminhtml\Category\AbstractCategory
{
    /**
     * Additional buttons on category page
     *
     * @var array
     */
    protected $_additionalButtons = array();

    /**
     * @var string
     */
    protected $_template = 'catalog/category/edit/form.phtml';

    /**
     * @var \Magento\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param Template\Context $context
     * @param \Magento\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Model\Resource\Category\Tree $categoryTree
     * @param \Magento\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\Resource\Category\Tree $categoryTree,
        \Magento\Registry $registry,
        \Magento\Json\EncoderInterface $jsonEncoder,
        array $data = array()
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context, $categoryTree, $registry, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->addChild(
                'magento-adminhtml-catalog-category-edit-js',
                'Magento\Theme\Block\Html\Head\Script',
                array('file' => 'Magento_Catalog::catalog/category/edit.js')
            );
        }

        $category = $this->getCategory();
        $categoryId = (int)$category->getId();
        // 0 when we create category, otherwise some value for editing category

        $this->setChild(
            'tabs',
            $this->getLayout()->createBlock('Magento\Catalog\Block\Adminhtml\Category\Tabs', 'tabs')
        );

        // Save button
        if (!$category->isReadonly()) {
            $this->addChild(
                'save_button',
                'Magento\Backend\Block\Widget\Button',
                array(
                    'label' => __('Save Category'),
                    'onclick' => "categorySubmit('" . $this->getSaveUrl() . "', true)",
                    'class' => 'save'
                )
            );
        }

        // Delete button
        if (!in_array($categoryId, $this->getRootIds()) && $category->isDeleteable()) {
            $this->addChild(
                'delete_button',
                'Magento\Backend\Block\Widget\Button',
                array(
                    'label' => __('Delete Category'),
                    'onclick' => "categoryDelete('" . $this->getUrl(
                        'catalog/*/delete',
                        array('_current' => true)
                    ) . "', true, {$categoryId})",
                    'class' => 'delete'
                )
            );
        }

        // Reset button
        if (!$category->isReadonly()) {
            $resetPath = $categoryId ? 'catalog/*/edit' : 'catalog/*/add';
            $this->addChild(
                'reset_button',
                'Magento\Backend\Block\Widget\Button',
                array(
                    'label' => __('Reset'),
                    'onclick' => "categoryReset('" . $this->getUrl($resetPath, array('_current' => true)) . "',true)"
                )
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getStoreConfigurationUrl()
    {
        $storeId = (int)$this->getRequest()->getParam('store');
        $params = array();
        //        $params = array('section'=>'catalog');
        if ($storeId) {
            $store = $this->_storeManager->getStore($storeId);
            $params['website'] = $store->getWebsite()->getCode();
            $params['store'] = $store->getCode();
        }
        return $this->getUrl('catalog/system_store', $params);
    }

    /**
     * @return string
     */
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    /**
     * @return string
     */
    public function getSaveButtonHtml()
    {
        if ($this->hasStoreRootCategory()) {
            return $this->getChildHtml('save_button');
        }
        return '';
    }

    /**
     * @return string
     */
    public function getResetButtonHtml()
    {
        if ($this->hasStoreRootCategory()) {
            return $this->getChildHtml('reset_button');
        }
        return '';
    }

    /**
     * Retrieve additional buttons html
     *
     * @return string
     */
    public function getAdditionalButtonsHtml()
    {
        $html = '';
        foreach ($this->_additionalButtons as $childName) {
            $html .= $this->getChildHtml($childName);
        }
        return $html;
    }

    /**
     * Add additional button
     *
     * @param string $alias
     * @param array $config
     * @return $this
     */
    public function addAdditionalButton($alias, $config)
    {
        if (isset($config['name'])) {
            $config['element_name'] = $config['name'];
        }
        $this->setChild(
            $alias . '_button',
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->addData($config)
        );
        $this->_additionalButtons[$alias] = $alias . '_button';
        return $this;
    }

    /**
     * Remove additional button
     *
     * @param string $alias
     * @return $this
     */
    public function removeAdditionalButton($alias)
    {
        if (isset($this->_additionalButtons[$alias])) {
            $this->unsetChild($this->_additionalButtons[$alias]);
            unset($this->_additionalButtons[$alias]);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getTabsHtml()
    {
        return $this->getChildHtml('tabs');
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        if ($this->hasStoreRootCategory()) {
            if ($this->getCategoryId()) {
                return $this->getCategoryName();
            } else {
                $parentId = (int)$this->getRequest()->getParam('parent');
                if ($parentId && $parentId != \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                    return __('New Subcategory');
                } else {
                    return __('New Root Category');
                }
            }
        }
        return __('Set Root Category for Store');
    }

    /**
     * @param array $args
     * @return string
     */
    public function getDeleteUrl(array $args = array())
    {
        $params = array('_current' => true);
        $params = array_merge($params, $args);
        return $this->getUrl('catalog/*/delete', $params);
    }

    /**
     * Return URL for refresh input element 'path' in form
     *
     * @param array $args
     * @return string
     */
    public function getRefreshPathUrl(array $args = array())
    {
        $params = array('_current' => true);
        $params = array_merge($params, $args);
        return $this->getUrl('catalog/*/refreshPath', $params);
    }

    /**
     * @return string
     */
    public function getProductsJson()
    {
        $products = $this->getCategory()->getProductsPosition();
        if (!empty($products)) {
            return $this->_jsonEncoder->encode($products);
        }
        return '{}';
    }

    /**
     * @return bool
     */
    public function isAjax()
    {
        return $this->_request->isXmlHttpRequest() || $this->_request->getParam('isAjax');
    }
}
