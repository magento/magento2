<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Category;

/**
 * Class Save
 */
class Save extends \Magento\Catalog\Controller\Adminhtml\Category
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JSONFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Controller\Result\JSONFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Controller\Result\JSONFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        parent::__construct($context, $resultRedirectFactory);
        $this->resultRawFactory = $resultRawFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * Filter category data
     *
     * @param array $rawData
     * @return array
     */
    protected function _filterCategoryPostData(array $rawData)
    {
        $data = $rawData;
        // @todo It is a workaround to prevent saving this data in category model and it has to be refactored in future
        if (isset($data['image']) && is_array($data['image'])) {
            $data['image_additional_data'] = $data['image'];
            unset($data['image']);
        }
        return $data;
    }

    /**
     * Category save
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $category = $this->_initCategory();

        if (!$category) {
            return $resultRedirect->setPath('catalog/*/', ['_current' => true, 'id' => null]);
        }

        $storeId = $this->getRequest()->getParam('store');
        $refreshTree = false;
        $data = $this->getRequest()->getPost();
        if ($data) {
            $category->addData($this->_filterCategoryPostData($data['general']));
            if (!$category->getId()) {
                $parentId = $this->getRequest()->getParam('parent');
                if (!$parentId) {
                    if ($storeId) {
                        $parentId = $this->_objectManager->get(
                            'Magento\Store\Model\StoreManagerInterface'
                        )->getStore(
                            $storeId
                        )->getRootCategoryId();
                    } else {
                        $parentId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
                    }
                }
                $parentCategory = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($parentId);
                $category->setPath($parentCategory->getPath());
                $category->setParentId($parentId);
            }

            /**
             * Process "Use Config Settings" checkboxes
             */
            $useConfig = $this->getRequest()->getPost('use_config');
            if ($useConfig) {
                foreach ($useConfig as $attributeCode) {
                    $category->setData($attributeCode, null);
                }
            }

            $category->setAttributeSetId($category->getDefaultAttributeSetId());

            if (isset($data['category_products']) && !$category->getProductsReadonly()) {
                $products = json_decode($data['category_products'], true);
                $category->setPostedProducts($products);
            }
            $this->_eventManager->dispatch(
                'catalog_category_prepare_save',
                ['category' => $category, 'request' => $this->getRequest()]
            );

            /**
             * Check "Use Default Value" checkboxes values
             */
            $useDefaults = $this->getRequest()->getPost('use_default');
            if ($useDefaults) {
                foreach ($useDefaults as $attributeCode) {
                    $category->setData($attributeCode, false);
                }
            }

            /**
             * Proceed with $_POST['use_config']
             * set into category model for processing through validation
             */
            $category->setData('use_post_data_config', $this->getRequest()->getPost('use_config'));

            try {
                $validate = $category->validate();
                if ($validate !== true) {
                    foreach ($validate as $code => $error) {
                        if ($error === true) {
                            $attribute = $category->getResource()->getAttribute($code)->getFrontend()->getLabel();
                            throw new \Magento\Framework\Model\Exception(__('Attribute "%1" is required.', $attribute));
                        } else {
                            throw new \Magento\Framework\Model\Exception($error);
                        }
                    }
                }

                $category->unsetData('use_post_data_config');
                if (isset($data['general']['entity_id'])) {
                    throw new \Magento\Framework\Model\Exception(__('Unable to save the category'));
                }

                $category->save();
                $this->messageManager->addSuccess(__('You saved the category.'));
                $refreshTree = true;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_getSession()->setCategoryData($data);
                $refreshTree = false;
            }
        }

        if ($this->getRequest()->getPost('return_session_messages_only')) {
            $category->load($category->getId());
            // to obtain truncated category name
            /** @var $block \Magento\Framework\View\Element\Messages */
            $block = $this->layoutFactory->create()->getMessagesBlock();
            $block->setMessages($this->messageManager->getMessages(true));

            /** @var \Magento\Framework\Controller\Result\JSON $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData(
                [
                    'messages' => $block->getGroupedHtml(),
                    'error' => !$refreshTree,
                    'category' => $category->toArray(),
                ]
            );
        }

        $redirectParams = [
            '_current' => true,
            'id' => $category->getId()
        ];
        if ($storeId) {
            $redirectParams['store'] = $storeId;
        }

        return $resultRedirect->setPath(
            'catalog/*/edit',
            $redirectParams
        );
    }
}
