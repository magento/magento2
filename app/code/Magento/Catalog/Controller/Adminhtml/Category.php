<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filter\FilterInput;
use Magento\Store\Model\Store;
use Magento\Framework\Controller\ResultFactory;

/**
 * Catalog category controller
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Category extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Catalog::categories';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    private $wysiwigConfig;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date|null $dateFilter
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwigConfig
     * @param \Magento\Backend\Model\Auth\Session $authSession
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        ?\Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter = null,
        ?\Magento\Store\Model\StoreManagerInterface $storeManager = null,
        ?\Magento\Framework\Registry $registry = null,
        ?\Magento\Cms\Model\Wysiwyg\Config $wysiwigConfig = null,
        ?\Magento\Backend\Model\Auth\Session $authSession = null
    ) {
        $this->dateFilter = $dateFilter;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(
            \Magento\Store\Model\StoreManagerInterface::class
        );
        $this->registry = $registry ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Registry::class
        );
        $this->wysiwigConfig = $wysiwigConfig ?: ObjectManager::getInstance()->get(
            \Magento\Cms\Model\Wysiwyg\Config::class
        );
        $this->authSession = $authSession ?: ObjectManager::getInstance()->get(
            \Magento\Backend\Model\Auth\Session::class
        );
        parent::__construct($context);
    }

    /**
     * Initialize requested category and put it into registry.
     *
     * Root category can be returned, if inappropriate store/category is specified
     *
     * @param bool $getRootInstead
     * @return \Magento\Catalog\Model\Category|false
     */
    protected function _initCategory($getRootInstead = false)
    {
        $categoryId = $this->resolveCategoryId();
        $storeId = $this->resolveStoreId();
        $category = $this->_objectManager->create(\Magento\Catalog\Model\Category::class);
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = $this->storeManager->getStore($storeId)->getRootCategoryId();
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

        $this->registry->unregister('category');
        $this->registry->unregister('current_category');
        $this->registry->register('category', $category);
        $this->registry->register('current_category', $category);
        $this->wysiwigConfig->setStoreId($storeId);
        return $category;
    }

    /**
     * Resolve Category Id (from get or from post)
     *
     * @return int
     */
    private function resolveCategoryId() : int
    {
        $categoryId = (int)$this->getRequest()->getParam('id', false);

        return $categoryId ?: (int)$this->getRequest()->getParam('entity_id', false);
    }

    /**
     * Resolve store Id, tries to take store id from store HTTP parameter
     *
     * @see Store
     *
     * @return int
     */
    private function resolveStoreId() : int
    {
        $storeId = (int)$this->getRequest()->getParam('store', false);

        return $storeId ?: (int)$this->getRequest()->getParam('store_id', Store::DEFAULT_STORE_ID);
    }

    /**
     * Build response for ajax request
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     *
     * @return \Magento\Framework\Controller\Result\Json
     *
     * @deprecated 101.0.0
     * @see we don't recommend this approach anymore
     */
    protected function ajaxRequestResponse($category, $resultPage)
    {
        // prepare breadcrumbs of selected category, if any
        $breadcrumbsPath = $category->getPath();
        if (empty($breadcrumbsPath)) {
            // but if no category, and it is deleted - prepare breadcrumbs from path, saved in session
            $breadcrumbsPath = $this->authSession->getDeletedPath(true);
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

        $eventResponse = new \Magento\Framework\DataObject(
            [
                'content' => $resultPage->getLayout()->getUiComponent('category_form')->getFormHtml()
                    . $resultPage->getLayout()->getBlock('category.tree')
                        ->getBreadcrumbsJavascript($breadcrumbsPath, 'editingCategoryBreadcrumbs'),
                'messages' => $resultPage->getLayout()->getMessagesBlock()->getGroupedHtml(),
                'toolbar' => $resultPage->getLayout()->getBlock('page.actions.toolbar')->toHtml()
            ]
        );
        $this->_eventManager->dispatch(
            'category_prepare_ajax_response',
            ['response' => $eventResponse, 'controller' => $this]
        );
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setHeader('Content-type', 'application/json', true);
        $resultJson->setData($eventResponse->getData());
        return $resultJson;
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
                    $dateFieldFilters[$attrKey] = $this->dateFilter;
                }
            }
        }
        $inputFilter = new FilterInput($dateFieldFilters, [], $postData);
        return $inputFilter->getUnescaped();
    }
}
