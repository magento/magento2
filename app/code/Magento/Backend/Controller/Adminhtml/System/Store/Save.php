<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Store;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Store\Model\Group as StoreGroup;
use Magento\Store\Model\Store;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Save
 *
 * Save controller for system entities such as: Store, StoreGroup, Website
 */
class Save extends \Magento\Backend\Controller\Adminhtml\System\Store implements HttpPostActionInterface
{
    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param FilterManager $filterManager
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FilterManager $filterManager,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        TypeListInterface $cacheTypeList,
    ) {
        parent::__construct($context, $coreRegistry, $filterManager, $resultForwardFactory, $resultPageFactory);
        $this->cacheTypeList = $cacheTypeList;
    }
    /**
     * Process Website model save
     *
     * @param array $postData
     * @return array
     */
    private function processWebsiteSave($postData)
    {
        $postData['website']['name'] = $this->filterManager->removeTags($postData['website']['name']);
        $websiteModel = $this->_objectManager->create(\Magento\Store\Model\Website::class);
        if ($postData['website']['website_id']) {
            $websiteModel->load($postData['website']['website_id']);
        }
        $websiteModel->setData($postData['website']);
        if ($postData['website']['website_id'] == '') {
            $websiteModel->setId(null);
        }

        $groupModel = $this->_objectManager->create(StoreGroup::class);
        $groupModel->load($websiteModel->getDefaultGroupId());
        $storeModel = $this->_objectManager->create(Store::class);
        $storeModel->load($groupModel->getDefaultStoreId());

        if ($websiteModel->getIsDefault() && !$storeModel->isActive()) {
            throw new LocalizedException(
                __('Please enable your Store View before using this Web Site as Default')
            );
        }

        $websiteModel->save();
        $this->messageManager->addSuccessMessage(__('You saved the website.'));

        return $postData;
    }

    /**
     * Process Store model save
     *
     * @param array $postData
     * @throws LocalizedException
     * @return array
     */
    private function processStoreSave($postData)
    {
        /** @var Store $storeModel */
        $storeModel = $this->_objectManager->create(Store::class);
        $postData['store']['name'] = $this->filterManager->removeTags($postData['store']['name']);
        if ($postData['store']['store_id']) {
            $storeModel->load($postData['store']['store_id']);
        }
        $originalCode = $storeModel->getCode();
        $newCode = $postData['store']['code'] ?? null;
        $storeModel->setData($postData['store']);
        if ($postData['store']['store_id'] == '') {
            $storeModel->setId(null);
        }
        $groupModel = $this->_objectManager->create(
            StoreGroup::class
        )->load(
            $storeModel->getGroupId()
        );
        $storeModel->setWebsiteId($groupModel->getWebsiteId());
        if (!$storeModel->isActive() && $storeModel->isDefault()) {
            throw new LocalizedException(
                __('The default store cannot be disabled')
            );
        }
        $storeModel->save();
        $this->messageManager->addSuccessMessage(__('You saved the store view.'));
        if ($originalCode !== $newCode) {
            $this->cacheTypeList->cleanType('config');
        }
        return $postData;
    }

    /**
     * Process StoreGroup model save
     *
     * @param array $postData
     * @throws LocalizedException
     * @return array
     */
    private function processGroupSave($postData)
    {
        $postData['group']['name'] = $this->filterManager->removeTags($postData['group']['name']);
        /** @var StoreGroup $groupModel */
        $groupModel = $this->_objectManager->create(StoreGroup::class);
        if ($postData['group']['group_id']) {
            $groupModel->load($postData['group']['group_id']);
        }
        $groupModel->setData($postData['group']);
        if ($postData['group']['group_id'] == '') {
            $groupModel->setId(null);
        }
        if (!$this->isSelectedDefaultStoreActive($postData, $groupModel)) {
            throw new LocalizedException(
                __('An inactive store view cannot be saved as default store view')
            );
        }

        $groupModel->save();
        $this->messageManager->addSuccessMessage(__('You saved the store.'));

        return $postData;
    }

    /**
     * Saving edited store information
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
        $redirectResult = $this->resultRedirectFactory->create();
        if ($this->getRequest()->isPost() && ($postData = $this->getRequest()->getPostValue())) {
            if (empty($postData['store_type']) || empty($postData['store_action'])) {
                $redirectResult->setPath('adminhtml/*/');
                return $redirectResult;
            }
            try {
                switch ($postData['store_type']) {
                    case 'website':
                        $postData = $this->processWebsiteSave($postData);
                        break;
                    case 'group':
                        $postData = $this->processGroupSave($postData);
                        break;
                    case 'store':
                        $postData = $this->processStoreSave($postData);
                        break;
                    default:
                        $redirectResult->setPath('adminhtml/*/');
                        return $redirectResult;
                }
                $redirectResult->setPath('adminhtml/*/');
                return $redirectResult;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_getSession()->setPostData($postData);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving. Please review the error log.')
                );
                $this->_getSession()->setPostData($postData);
            }
            $redirectResult->setUrl($this->_redirect->getRedirectUrl($this->getUrl('*')));
            return $redirectResult;
        }
        $redirectResult->setPath('adminhtml/*/');
        return $redirectResult;
    }

    /**
     * Verify if selected default store is active
     *
     * @param array $postData
     * @param StoreGroup $groupModel
     * @return bool
     */
    private function isSelectedDefaultStoreActive(array $postData, StoreGroup $groupModel)
    {
        if (!empty($postData['group']['default_store_id'])) {
            $defaultStoreId = $postData['group']['default_store_id'];
            if (!empty($groupModel->getStores()[$defaultStoreId]) &&
                !$groupModel->getStores()[$defaultStoreId]->isActive()
            ) {
                return false;
            }
        }
        return true;
    }
}
