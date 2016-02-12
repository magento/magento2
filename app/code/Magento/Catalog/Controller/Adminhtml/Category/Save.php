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
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * The list of inputs that need to convert from string to boolean
     * @var array
     */
    protected $stringToBoolInputs = [
        'general' => [
            'custom_use_parent_settings',
            'custom_apply_to_products',
            'is_active',
            'include_in_menu',
            'is_anchor',
            'use_default' => ['url_key'],
            'use_config' => ['available_sort_by', 'filter_price_range', 'default_sort_by'],
            'savedImage' => ['delete']
        ]
    ];

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $category = $this->_initCategory();

        if (!$category) {
            return $resultRedirect->setPath('catalog/*/', ['_current' => true, 'id' => null]);
        }

        $data['general'] = $this->getRequest()->getPostValue();
        $isNewCategory = !isset($data['general']['entity_id']);
        $data = $this->stringToBoolConverting($data);
        $data = $this->imagePreprocessing($data);
        $storeId = isset($data['general']['store_id']) ? $data['general']['store_id'] : null;
        $parentId = isset($data['general']['parent']) ? $data['general']['parent'] : null;
        if ($data['general']) {
            $category->addData($this->_filterCategoryPostData($data['general']));
            if ($isNewCategory) {
                $parentCategory = $this->getParentCategory($parentId, $storeId);
                $category->setPath($parentCategory->getPath());
                $category->setParentId($parentCategory->getId());
            }

            /**
             * Process "Use Config Settings" checkboxes
             */
            $generalPost = $data['general'];
            $useConfig = [];
            if (isset($generalPost['use_config']) && !empty($generalPost['use_config'])) {
                foreach ($generalPost['use_config'] as $attributeCode => $attributeValue) {
                    if ($attributeValue) {
                        $useConfig[] = $attributeCode;
                    }
                }
                foreach ($useConfig as $attributeCode) {
                    $category->setData($attributeCode, null);
                }
            }

            $category->setAttributeSetId($category->getDefaultAttributeSetId());

            if (isset($data['general']['category_products'])
                && is_string($data['general']['category_products'])
                && !$category->getProductsReadonly()
            ) {
                $products = json_decode($data['general']['category_products'], true);
                $category->setPostedProducts($products);
            }
            $this->_eventManager->dispatch(
                'catalog_category_prepare_save',
                ['category' => $category, 'request' => $this->getRequest()]
            );

            /**
             * Check "Use Default Value" checkboxes values
             */
            if (isset($generalPost['use_default']) && !empty($generalPost['use_default'])) {
                foreach ($generalPost['use_default'] as $attributeCode => $attributeValue) {
                    if ($attributeValue) {
                        $category->setData($attributeCode, false);
                    }
                }
            }

            /**
             * Proceed with $_POST['use_config']
             * set into category model for processing through validation
             */
            $category->setData('use_post_data_config', $useConfig);

            try {
                $categoryResource = $category->getResource();
                if ($category->hasCustomDesignTo()) {
                    $categoryResource->getAttribute('custom_design_from')->setMaxValue($category->getCustomDesignTo());
                }
                $validate = $category->validate();
                if ($validate !== true) {
                    foreach ($validate as $code => $error) {
                        if ($error === true) {
                            $attribute = $categoryResource->getAttribute($code)->getFrontend()->getLabel();
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __('Attribute "%1" is required.', $attribute)
                            );
                        } else {
                            throw new \Magento\Framework\Exception\LocalizedException(__($error));
                        }
                    }
                }

                $category->unsetData('use_post_data_config');

                $category->save();
                $this->messageManager->addSuccess(__('You saved the category.'));
            } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                $this->_getSession()->setCategoryData($data);
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong while saving the category.'));
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                $this->_getSession()->setCategoryData($data);
            }
        }

        $hasError = (bool)$this->messageManager->getMessages()->getCountByType(
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );

        if ($this->getRequest()->getPost('return_session_messages_only')) {
            $category->load($category->getId());
            // to obtain truncated category name
            /** @var $block \Magento\Framework\View\Element\Messages */
            $block = $this->layoutFactory->create()->getMessagesBlock();
            $block->setMessages($this->messageManager->getMessages(true));

            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData(
                [
                    'messages' => $block->getGroupedHtml(),
                    'error' => $hasError,
                    'category' => $category->toArray(),
                ]
            );
        }

        $redirectParams = $this->getRedirectParams($isNewCategory, $hasError, $category->getId(), $parentId, $storeId);

        return $resultRedirect->setPath(
            $redirectParams['path'],
            $redirectParams['params']
        );
    }

    /**
     * Image data preprocessing
     *
     * @param array $data
     *
     * @return array
     */
    public function imagePreprocessing($data)
    {
        if (!isset($_FILES) || (isset($_FILES['image']) && $_FILES['image']['name'] === '' )) {
            unset($data['general']['image']);
            if (
                isset($data['general']['savedImage']['delete']) &&
                $data['general']['savedImage']['delete']
            ) {
                $data['general']['image']['delete'] = $data['general']['savedImage']['delete'];
            }
        }
        return $data;
    }

    /**
     * Converting inputs from string to boolean
     *
     * @param array $data
     * @param array $stringToBoolInputs
     *
     * @return array
     */
    public function stringToBoolConverting($data, $stringToBoolInputs = null)
    {
        if (null === $stringToBoolInputs) {
            $stringToBoolInputs = $this->stringToBoolInputs;
        }
        foreach ($stringToBoolInputs as $key => $value) {
            if (is_array($value)) {
                if (isset($data[$key])) {
                    $data[$key] = $this->stringToBoolConverting($data[$key], $value);
                }
            } else {
                if (isset($data[$value])) {
                    if ($data[$value] === 'true') {
                        $data[$value] = true;
                    }
                    if ($data[$value] === 'false') {
                        $data[$value] = false;
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Get parent category
     *
     * @param int $parentId
     * @param int $storeId
     *
     * @return \Magento\Catalog\Model\Category
     */
    protected function getParentCategory($parentId, $storeId)
    {
        if (!$parentId) {
            if ($storeId) {
                $parentId = $this->_objectManager->get(
                    \Magento\Store\Model\StoreManagerInterface::class
                )->getStore(
                    $storeId
                )->getRootCategoryId();
            } else {
                $parentId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
            }
        }
        return $this->_objectManager->create(\Magento\Catalog\Model\Category::class)->load($parentId);
    }

    /**
     * Get category redirect path
     *
     * @param bool $isNewCategory
     * @param bool $hasError
     * @param int $categoryId
     * @param int $parentId
     * @param int $storeId
     *
     * @return array
     */
    protected function getRedirectParams($isNewCategory, $hasError, $categoryId, $parentId, $storeId)
    {
        $params = ['_current' => true];
        if ($storeId) {
            $params['store'] = $storeId;
        }
        if ($isNewCategory && $hasError) {
            $path = 'catalog/*/add';
            $params['parent'] = $parentId;
        } else {
            $path = 'catalog/*/edit';
            $params['id'] = $categoryId;

        }
        return ['path' => $path, 'params' => $params];
    }
}
