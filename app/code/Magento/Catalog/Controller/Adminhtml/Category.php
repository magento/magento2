<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml;

/**
 * Catalog category controller
 */
abstract class Category extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::categories';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\DateTime
     */
    private $dateTimeFilter;

    /**
     * Initialize requested category and put it into registry.
     * Root category can be returned, if inappropriate store/category is specified
     *
     * @param bool $getRootInstead
     * @return \Magento\Catalog\Model\Category|false
     */
    protected function _initCategory($getRootInstead = false)
    {
        $categoryId = $this->resolveCategoryId();
        $storeId = (int)$this->getRequest()->getParam('store');
        $category = $this->_objectManager->create(\Magento\Catalog\Model\Category::class);
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = $this->_objectManager->get(
                    \Magento\Store\Model\StoreManagerInterface::class
                )->getStore(
                    $storeId
                )->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    // load root category instead wrong one
                    if ($getRootInstead) {
                        $category->load($rootId);
                    } else {
                        return false;
                    }
                }
            }
        }

        $this->_objectManager->get(\Magento\Framework\Registry::class)->register('category', $category);
        $this->_objectManager->get(\Magento\Framework\Registry::class)->register('current_category', $category);
        $this->_objectManager->get(\Magento\Cms\Model\Wysiwyg\Config::class)
            ->setStoreId($this->getRequest()->getParam('store'));
        return $category;
    }

    /**
     * Resolve Category Id (from get or from post)
     *
     * @return int
     */
    private function resolveCategoryId()
    {
        $categoryId = (int)$this->getRequest()->getParam('id', false);

        return $categoryId ?: (int)$this->getRequest()->getParam('entity_id', false);
    }

    /**
     * Build response for ajax request
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     *
     * @return \Magento\Framework\Controller\Result\Json
     *
     * @deprecated
     */
    protected function ajaxRequestResponse($category, $resultPage)
    {
        // prepare breadcrumbs of selected category, if any
        $breadcrumbsPath = $category->getPath();
        if (empty($breadcrumbsPath)) {
            // but if no category, and it is deleted - prepare breadcrumbs from path, saved in session
            $breadcrumbsPath = $this->_objectManager->get(
                \Magento\Backend\Model\Auth\Session::class
            )->getDeletedPath(
                true
            );
            if (!empty($breadcrumbsPath)) {
                $breadcrumbsPath = explode('/', $breadcrumbsPath);
                // no need to get parent breadcrumbs if deleting category level 1
                if (count($breadcrumbsPath) <= 1) {
                    $breadcrumbsPath = '';
                } else {
                    array_pop($breadcrumbsPath);
                    $breadcrumbsPath = implode('/', $breadcrumbsPath);
                }
            }
        }

        $eventResponse = new \Magento\Framework\DataObject([
            'content' => $resultPage->getLayout()->getUiComponent('category_form')->getFormHtml()
                . $resultPage->getLayout()->getBlock('category.tree')
                    ->getBreadcrumbsJavascript($breadcrumbsPath, 'editingCategoryBreadcrumbs'),
            'messages' => $resultPage->getLayout()->getMessagesBlock()->getGroupedHtml(),
            'toolbar' => $resultPage->getLayout()->getBlock('page.actions.toolbar')->toHtml()
        ]);
        $this->_eventManager->dispatch(
            'category_prepare_ajax_response',
            ['response' => $eventResponse, 'controller' => $this]
        );
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->_objectManager->get(\Magento\Framework\Controller\Result\Json::class);
        $resultJson->setHeader('Content-type', 'application/json', true);
        $resultJson->setData($eventResponse->getData());
        return $resultJson;
    }

    /**
     * @return \Magento\Framework\Stdlib\DateTime\Filter\DateTime
     *
     * @deprecated
     */
    private function getDateTimeFilter()
    {
        if ($this->dateTimeFilter === null) {
            $this->dateTimeFilter = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Stdlib\DateTime\Filter\DateTime::class);
        }
        return $this->dateTimeFilter;
    }

    /**
     * Datetime data preprocessing
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param array $postData
     *
     * @return array
     */
    protected function dateTimePreprocessing($category, $postData)
    {
        $dateFieldFilters = [];
        $attributes = $category->getAttributes();
        foreach ($attributes as $attrKey => $attribute) {
            if ($attribute->getBackend()->getType() == 'datetime') {
                if (array_key_exists($attrKey, $postData) && $postData[$attrKey] != '') {
                    $dateFieldFilters[$attrKey] = $this->getDateTimeFilter();
                }
            }
        }
        $inputFilter = new \Zend_Filter_Input($dateFieldFilters, [], $postData);
        return $inputFilter->getUnescaped();
    }
}
