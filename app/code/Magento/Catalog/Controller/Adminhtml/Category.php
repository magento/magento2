<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml;

use Magento\Store\Model\Store;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Backend\App\Action;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Registry;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\Result\Json;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\DataObject;

/**
 * Catalog category controller
 */
abstract class Category extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::categories';

    /**
     * @var Date
     */
    protected $dateFilter;

    /**
     * @param Context $context
     * @param Date|null $dateFilter
     */
    public function __construct(Context $context, Date $dateFilter = null)
    {
        $this->dateFilter = $dateFilter;
        parent::__construct($context);
    }

    /**
     * Initialize requested category and put it into registry.
     * Root category can be returned, if inappropriate store/category is specified
     *
     * @param bool $getRootInstead
     * @return CategoryModel|false
     */
    protected function _initCategory(bool $getRootInstead = false)
    {
        $categoryId = $this->resolveCategoryId();
        $storeId = $this->resolveStoreId();
        $category = $this->_objectManager->create(CategoryModel::class);
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = $this->_objectManager->get(
                    StoreManagerInterface::class
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

        $this->_objectManager->get(Registry::class)->register('category', $category);
        $this->_objectManager->get(Registry::class)->register('current_category', $category);
        $this->_objectManager->get(Config::class)
            ->setStoreId($this->getRequest()->getParam('store'));
        return $category;
    }

    /**
     * Resolve Category Id (from get or from post)
     *
     * @return int
     */
    private function resolveCategoryId(): int
    {
        $categoryId = (int)$this->getRequest()->getParam('id', false);

        return $categoryId ?: (int)$this->getRequest()->getParam('entity_id', false);
    }

    /**
     * Resolve store id
     *
     * Tries to take store id from store HTTP parameter
     * @see Store
     *
     * @return int
     */
    private function resolveStoreId(): int
    {
        $storeId = (int)$this->getRequest()->getParam('store', false);

        return $storeId ?: (int)$this->getRequest()->getParam('store_id', Store::DEFAULT_STORE_ID);
    }

    /**
     * Build response for ajax request
     *
     * @param CategoryModel $category
     * @param Page $resultPage
     *
     * @return Json
     *
     * @deprecated 101.0.0
     */
    protected function ajaxRequestResponse(CategoryModel $category, Page $resultPage): Json
    {
        // prepare breadcrumbs of selected category, if any
        $breadcrumbsPath = $category->getPath();
        if (empty($breadcrumbsPath)) {
            // but if no category, and it is deleted - prepare breadcrumbs from path, saved in session
            $breadcrumbsPath = $this->_objectManager->get(
                Session::class
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

        $eventResponse = new DataObject([
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
        /** @var Json $resultJson */
        $resultJson = $this->_objectManager->get(Json::class);
        $resultJson->setHeader('Content-type', 'application/json', true);
        $resultJson->setData($eventResponse->getData());

        return $resultJson;
    }

    /**
     * Datetime data preprocessing
     *
     * @param CategoryModel $category
     * @param array $postData
     *
     * @return array
     */
    protected function dateTimePreprocessing(CategoryModel $category, array $postData): array
    {
        $dateFieldFilters = [];
        $attributes = $category->getAttributes();
        foreach ($attributes as $attrKey => $attribute) {
            if ($attribute->getBackend()->getType() == 'datetime') {
                if (array_key_exists($attrKey, $postData) && $postData[$attrKey] != '') {
                    $dateFieldFilters[$attrKey] = $this->dateFilter;
                }
            }
        }
        $inputFilter = new \Zend_Filter_Input($dateFieldFilters, [], $postData);
        return $inputFilter->getUnescaped();
    }
}
