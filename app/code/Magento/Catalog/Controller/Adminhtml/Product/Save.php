<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class Save
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Catalog\Controller\Adminhtml\Product implements HttpPostActionInterface
{
    /**
     * @var Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Copier
     */
    protected $productCopier;

    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    protected $productTypeManager;

    /**
     * @var \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    protected $categoryLinkManagement;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Escaper|null
     */
    private $escaper;

    /**
     * @var null|\Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param Builder $productBuilder
     * @param Initialization\Helper $initializationHelper
     * @param \Magento\Catalog\Model\Product\Copier $productCopier
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Escaper|null $escaper
     * @param \Psr\Log\LoggerInterface|null $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Product\Builder $productBuilder,
        Initialization\Helper $initializationHelper,
        \Magento\Catalog\Model\Product\Copier $productCopier,
        \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Escaper $escaper = null,
        \Psr\Log\LoggerInterface $logger = null
    ) {
        $this->initializationHelper = $initializationHelper;
        $this->productCopier = $productCopier;
        $this->productTypeManager = $productTypeManager;
        $this->productRepository = $productRepository;
        parent::__construct($context, $productBuilder);
        $this->escaper = $escaper ?? $this->_objectManager->get(\Magento\Framework\Escaper::class);
        $this->logger = $logger ?? $this->_objectManager->get(\Psr\Log\LoggerInterface::class);
    }

    /**
     * Save product action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store', 0);
        $store = $this->getStoreManager()->getStore($storeId);
        $this->getStoreManager()->setCurrentStore($store->getCode());
        $redirectBack = $this->getRequest()->getParam('back', false);
        $productId = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        $productAttributeSetId = $this->getRequest()->getParam('set');
        $productTypeId = $this->getRequest()->getParam('type');
        if ($data) {
            try {
                $product = $this->initializationHelper->initialize(
                    $this->productBuilder->build($this->getRequest())
                );
                $this->productTypeManager->processProduct($product);
                if (isset($data['product'][$product->getIdFieldName()])) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The product was unable to be saved. Please try again.')
                    );
                }

                $originalSku = $product->getSku();
                $canSaveCustomOptions = $product->getCanSaveCustomOptions();
                $product->save();
                $this->handleImageRemoveError($data, $product->getId());
                $this->getCategoryLinkManagement()->assignProductToCategories(
                    $product->getSku(),
                    $product->getCategoryIds()
                );
                $productId = $product->getEntityId();
                $productAttributeSetId = $product->getAttributeSetId();
                $productTypeId = $product->getTypeId();
                $extendedData = $data;
                $extendedData['can_save_custom_options'] = $canSaveCustomOptions;
                $this->copyToStores($extendedData, $productId);
                $this->messageManager->addSuccessMessage(__('You saved the product.'));
                $this->getDataPersistor()->clear('catalog_product');
                if ($product->getSku() != $originalSku) {
                    $this->messageManager->addNoticeMessage(
                        __(
                            'SKU for product %1 has been changed to %2.',
                            $this->escaper->escapeHtml($product->getName()),
                            $this->escaper->escapeHtml($product->getSku())
                        )
                    );
                }
                $this->_eventManager->dispatch(
                    'controller_action_catalog_product_save_entity_after',
                    ['controller' => $this, 'product' => $product]
                );

                if ($redirectBack === 'duplicate') {
                    $product->unsetData('quantity_and_stock_status');
                    $newProduct = $this->productCopier->copy($product);
                    $this->checkUniqueAttributes($product);
                    $this->messageManager->addSuccessMessage(__('You duplicated the product.'));
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->critical($e);
                $this->messageManager->addExceptionMessage($e);
                $data = isset($product) ? $this->persistMediaData($product, $data) : $data;
                $this->getDataPersistor()->set('catalog_product', $data);
                $redirectBack = $productId ? true : 'new';
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage($e->getMessage());
                $data = isset($product) ? $this->persistMediaData($product, $data) : $data;
                $this->getDataPersistor()->set('catalog_product', $data);
                $redirectBack = $productId ? true : 'new';
            }
        } else {
            $resultRedirect->setPath('catalog/*/', ['store' => $storeId]);
            $this->messageManager->addErrorMessage('No data to save');
            return $resultRedirect;
        }

        if ($redirectBack === 'new') {
            $resultRedirect->setPath(
                'catalog/*/new',
                ['set' => $productAttributeSetId, 'type' => $productTypeId]
            );
        } elseif ($redirectBack === 'duplicate' && isset($newProduct)) {
            $resultRedirect->setPath(
                'catalog/*/edit',
                ['id' => $newProduct->getEntityId(), 'back' => null, '_current' => true]
            );
        } elseif ($redirectBack) {
            $resultRedirect->setPath(
                'catalog/*/edit',
                ['id' => $productId, '_current' => true, 'set' => $productAttributeSetId]
            );
        } else {
            $resultRedirect->setPath('catalog/*/', ['store' => $storeId]);
        }
        return $resultRedirect;
    }

    /**
     * Notify customer when image was not deleted in specific case.
     *
     * TODO: temporary workaround must be eliminated in MAGETWO-45306
     *
     * @param array $postData
     * @param int $productId
     * @return void
     */
    private function handleImageRemoveError($postData, $productId)
    {
        if (isset($postData['product']['media_gallery']['images'])) {
            $removedImagesAmount = 0;
            foreach ($postData['product']['media_gallery']['images'] as $image) {
                if (!empty($image['removed'])) {
                    $removedImagesAmount++;
                }
            }
            if ($removedImagesAmount) {
                $expectedImagesAmount = count($postData['product']['media_gallery']['images']) - $removedImagesAmount;
                $product = $this->productRepository->getById($productId);
                $images = $product->getMediaGallery('images');
                if (is_array($images) && $expectedImagesAmount != count($images)) {
                    $this->messageManager->addNoticeMessage(
                        __('The image cannot be removed as it has been assigned to the other image role')
                    );
                }
            }
        }
    }

    /**
     * Do copying data to stores
     *
     * If the 'copy_from' field is not specified in the input data,
     * the store fallback mechanism will automatically take the admin store's default value.
     *
     * @param array $data
     * @param int $productId
     * @return void
     */
    protected function copyToStores($data, $productId)
    {
        if (!empty($data['product']['copy_to_stores'])) {
            foreach ($data['product']['copy_to_stores'] as $websiteId => $group) {
                if (isset($data['product']['website_ids'][$websiteId])
                    && (bool)$data['product']['website_ids'][$websiteId]) {
                    foreach ($group as $store) {
                        if (isset($store['copy_from'])) {
                            $copyFrom = $store['copy_from'];
                            $copyTo = (isset($store['copy_to'])) ? $store['copy_to'] : 0;
                            if ($copyTo) {
                                $this->_objectManager->create(\Magento\Catalog\Model\Product::class)
                                    ->setStoreId($copyFrom)
                                    ->load($productId)
                                    ->setStoreId($copyTo)
                                    ->setCanSaveCustomOptions($data['can_save_custom_options'])
                                    ->setCopyFromView(true)
                                    ->save();
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Get categoryLinkManagement in a backward compatible way.
     *
     * @return \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    private function getCategoryLinkManagement()
    {
        if (null === $this->categoryLinkManagement) {
            $this->categoryLinkManagement = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\Api\CategoryLinkManagementInterface::class);
        }
        return $this->categoryLinkManagement;
    }

    /**
     * Get storeManager in a backward compatible way.
     *
     * @return StoreManagerInterface
     * @deprecated 101.0.0
     */
    private function getStoreManager()
    {
        if (null === $this->storeManager) {
            $this->storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Store\Model\StoreManagerInterface::class);
        }
        return $this->storeManager;
    }

    /**
     * Retrieve data persistor
     *
     * @return DataPersistorInterface|mixed
     * @deprecated 101.0.0
     */
    protected function getDataPersistor()
    {
        if (null === $this->dataPersistor) {
            $this->dataPersistor = $this->_objectManager->get(DataPersistorInterface::class);
        }

        return $this->dataPersistor;
    }

    /**
     * Persist media gallery on error, in order to show already saved images on next run.
     *
     * @param ProductInterface $product
     * @param array $data
     * @return array
     */
    private function persistMediaData(ProductInterface $product, array $data)
    {
        $mediaGallery = $product->getData('media_gallery');
        if (!empty($mediaGallery['images'])) {
            foreach ($mediaGallery['images'] as $key => $image) {
                if (!isset($image['new_file'])) {
                    //Remove duplicates.
                    unset($mediaGallery['images'][$key]);
                }
            }
            $data['product']['media_gallery'] = $mediaGallery;
            $fields = [
                'image',
                'small_image',
                'thumbnail',
                'swatch_image',
            ];
            foreach ($fields as $field) {
                $data['product'][$field] = $product->getData($field);
            }
        }

        return $data;
    }

    /**
     * Check unique attributes and add error to message manager
     *
     * @param \Magento\Catalog\Model\Product $product
     */
    private function checkUniqueAttributes(\Magento\Catalog\Model\Product $product)
    {
        $uniqueLabels = [];
        foreach ($product->getAttributes() as $attribute) {
            if ($attribute->getIsUnique() && $attribute->getIsUserDefined()
                && $product->getData($attribute->getAttributeCode()) !== null
            ) {
                $uniqueLabels[] = $attribute->getDefaultFrontendLabel();
            }
        }
        if ($uniqueLabels) {
            $uniqueLabels = implode('", "', $uniqueLabels);
            $this->messageManager->addErrorMessage(__('The value of attribute(s) "%1" must be unique', $uniqueLabels));
        }
    }
}
