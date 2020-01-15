<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Set;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Set;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Messages;
use Magento\Framework\View\LayoutFactory;

/**
 * Save attribute set controller.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends Set implements HttpPostActionInterface
{
    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var SetFactory
     */
    private $attributeSetFactory;

    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @var Data
     */
    private $jsonHelper;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param LayoutFactory $layoutFactory
     * @param JsonFactory $resultJsonFactory
     * @param SetFactory $attributeSetFactory
     * @param FilterManager $filterManager
     * @param Data $jsonHelper
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        SetFactory $attributeSetFactory,
        FilterManager $filterManager,
        Data $jsonHelper
    ) {
        parent::__construct($context, $coreRegistry);
        $this->layoutFactory = $layoutFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->filterManager = $filterManager;
        $this->jsonHelper = $jsonHelper;
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
     * @return ResultInterface
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
                    throw new LocalizedException(
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
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $hasError = true;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the attribute set.'));
            $hasError = true;
        }

        if ($isNewSet) {
            if ($this->getRequest()->getPost('return_session_messages_only')) {
                /** @var $block Messages */
                $block = $this->layoutFactory->create()->getMessagesBlock();
                $block->setMessages($this->messageManager->getMessages(true));
                $body = [
                    'messages' => $block->getGroupedHtml(),
                    'error' => $hasError,
                    'id' => $model->getId(),
                ];
                return $this->resultJsonFactory->create()->setData($body);
            }

            $resultRedirect = $this->resultRedirectFactory->create();
            if ($hasError) {
                $resultRedirect->setPath('catalog/*/add');
            } else {
                $resultRedirect->setPath('catalog/*/edit', ['id' => $model->getId()]);
            }
            return $resultRedirect;
        }

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
