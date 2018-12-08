<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Controller\Adminhtml\AbstractAction;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
<<<<<<< HEAD
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Element\UiComponentFactory;
=======
use Magento\Ui\Model\UiComponentTypeResolver;
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
use Psr\Log\LoggerInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Render a component.
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Render extends AbstractAction
{
    /**
<<<<<<< HEAD
=======
     * @var \Magento\Ui\Model\UiComponentTypeResolver
     */
    private $contentTypeResolver;

    /**
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param UiComponentFactory $factory
<<<<<<< HEAD
=======
     * @param UiComponentTypeResolver $contentTypeResolver
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     * @param JsonFactory|null $resultJsonFactory
     * @param Escaper|null $escaper
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Context $context,
        UiComponentFactory $factory,
<<<<<<< HEAD
=======
        UiComponentTypeResolver $contentTypeResolver,
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        JsonFactory $resultJsonFactory = null,
        Escaper $escaper = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context, $factory);
<<<<<<< HEAD
=======
        $this->contentTypeResolver = $contentTypeResolver;
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        $this->resultJsonFactory = $resultJsonFactory ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Controller\Result\JsonFactory::class);
        $this->escaper = $escaper ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Escaper::class);
        $this->logger = $logger ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Psr\Log\LoggerInterface::class);
    }

    /**
<<<<<<< HEAD
     * Action for AJAX request.
     *
     * @return void|\Magento\Framework\Controller\ResultInterface
=======
     * @inheritdoc
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    public function execute()
    {
        if ($this->_request->getParam('namespace') === null) {
            $this->_redirect('admin/noroute');

            return;
        }

        try {
<<<<<<< HEAD
            $component = $this->factory->create($this->_request->getParam('namespace'));
            if ($this->validateAclResource($component->getContext()->getDataProvider()->getConfigData())) {
                $this->prepareComponent($component);

                if ($component->getContext()->getAcceptType() === 'json') {
                    $this->_response->setHeader('Content-Type', 'application/json');
                }

                $this->_response->appendBody((string) $component->render());
=======
            $component = $this->factory->create($this->getRequest()->getParam('namespace'));
            if ($this->validateAclResource($component->getContext()->getDataProvider()->getConfigData())) {
                $this->prepareComponent($component);
                $this->getResponse()->appendBody((string)$component->render());

                $contentType = $this->contentTypeResolver->resolve($component->getContext());
                $this->getResponse()->setHeader('Content-Type', $contentType, true);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
            $result = [
                'error' => $this->escaper->escapeHtml($e->getMessage()),
                'errorcode' => $this->escaper->escapeHtml($e->getCode())
            ];
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setStatusHeader(
                \Zend\Http\Response::STATUS_CODE_400,
                \Zend\Http\AbstractMessage::VERSION_11,
                'Bad Request'
            );
<<<<<<< HEAD
=======

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            return $resultJson->setData($result);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $result = [
                'error' => __('UI component could not be rendered because of system exception'),
                'errorcode' => $this->escaper->escapeHtml($e->getCode())
            ];
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setStatusHeader(
                \Zend\Http\Response::STATUS_CODE_400,
                \Zend\Http\AbstractMessage::VERSION_11,
                'Bad Request'
            );
<<<<<<< HEAD
=======

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            return $resultJson->setData($result);
        }
    }

    /**
     * Call prepare method in the component UI.
     *
     * @param UiComponentInterface $component
     * @return void
     */
    protected function prepareComponent(UiComponentInterface $component)
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareComponent($child);
        }

        $component->prepare();
    }

    /**
     * Optionally validate ACL resource of components with a DataSource/DataProvider.
     *
     * @param mixed $dataProviderConfigData
     * @return bool
     */
    private function validateAclResource($dataProviderConfigData)
    {
        if (isset($dataProviderConfigData['aclResource'])) {
            if (!$this->_authorization->isAllowed($dataProviderConfigData['aclResource'])) {
                if (!$this->_request->isAjax()) {
                    $this->_redirect('admin/denied');
                }

                return false;
            }
        }

        return true;
    }
}
