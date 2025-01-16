<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Set;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Catalog\Controller\Adminhtml\Product\Set implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    private $attributeSetFactory;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $filterManager;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        ?\Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory = null,
        ?\Magento\Framework\Filter\FilterManager $filterManager = null,
        ?\Magento\Framework\Json\Helper\Data $jsonHelper = null
    ) {
        parent::__construct($context, $coreRegistry);
        $this->layoutFactory = $layoutFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->attributeSetFactory =  $attributeSetFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Eav\Model\Entity\Attribute\SetFactory::class);
        $this->filterManager =  $filterManager ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\Filter\FilterManager::class);
        $this->jsonHelper =  $jsonHelper ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\Json\Helper\Data::class);
    }

    /**
     * Retrieve catalog product entity type id
     *
     * @return int
     */
    protected function _getEntityTypeId()
    {
        if ($this->_coreRegistry->registry('entityType') === null) {
            $this->_setTypeId();
        }
        return $this->_coreRegistry->registry('entityType');
    }

    /**
     * Save attribute set action
     *
     * [POST] Create attribute set from another set and redirect to edit page
     * [AJAX] Save attribute set data
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $entityTypeId = $this->_getEntityTypeId();
        $hasError = false;
        $attributeSetId = $this->getRequest()->getParam('id', false);
        $isNewSet = $this->getRequest()->getParam('gotoEdit', false) == '1';

        /* @var $model \Magento\Eav\Model\Entity\Attribute\Set */
        $model = $this->attributeSetFactory->create()->setEntityTypeId($entityTypeId);

        try {
            if ($isNewSet) {
                //filter html tags
                $name = $this->filterManager->stripTags($this->getRequest()->getParam('attribute_set_name'));
                $model->setAttributeSetName(trim($name));
            } else {
                if ($attributeSetId) {
                    $model->load($attributeSetId);
                }
                if (!$model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('This attribute set no longer exists.')
                    );
                }
                $data = $this->jsonHelper->jsonDecode($this->getRequest()->getPost('data'));

                //filter html tags
                $data['attribute_set_name'] = $this->filterManager->stripTags($data['attribute_set_name']);

                $model->organizeData($data);
            }

            $model->validate();
            if ($isNewSet) {
                $model->save();
                $model->initFromSkeleton($this->getRequest()->getParam('skeleton_set'));
            }
            $model->save();
            $this->messageManager->addSuccessMessage(__('You saved the attribute set.'));
        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $hasError = true;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $hasError = true;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the attribute set.'));
            $hasError = true;
        }

        if ($isNewSet) {
            if ($this->getRequest()->getPost('return_session_messages_only')) {
                /** @var $block \Magento\Framework\View\Element\Messages */
                $block = $this->layoutFactory->create()->getMessagesBlock();
                $block->setMessages($this->messageManager->getMessages(true));
                $body = [
                    'messages' => $block->getGroupedHtml(),
                    'error' => $hasError,
                    'id' => $model->getId(),
                ];
                return $this->resultJsonFactory->create()->setData($body);
            } else {
                $resultRedirect = $this->resultRedirectFactory->create();
                if ($hasError) {
                    $resultRedirect->setPath('catalog/*/add');
                } else {
                    $resultRedirect->setPath('catalog/*/edit', ['id' => $model->getId()]);
                }
                return $resultRedirect;
            }
        } else {
            $response = [];
            if ($hasError) {
                $layout = $this->layoutFactory->create();
                $layout->initMessages();
                $response['error'] = 1;
                $response['message'] = $layout->getMessagesBlock()->getGroupedHtml();
            } else {
                $response['error'] = 0;
                $response['url'] = $this->getUrl('catalog/*/');
            }
            return $this->resultJsonFactory->create()->setData($response);
        }
    }
}
