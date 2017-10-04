<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller\Adminhtml\Index;

use Magento\Ui\Controller\Adminhtml\AbstractAction;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Element\UiComponentFactory;

class Render extends AbstractAction
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param UiComponentFactory $factory
     * @param \Magento\Framework\Controller\Result\JsonFactory|null $resultJsonFactory
     * @param \Magento\Framework\Escaper|null $escaper
     * @param \Psr\Log\LoggerInterface|null $logger
     */
    public function __construct(
        Context $context,
        UiComponentFactory $factory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory = null,
        \Magento\Framework\Escaper $escaper = null,
        \Psr\Log\LoggerInterface $logger = null
    ) {
        parent::__construct($context, $factory);
        $this->resultJsonFactory = $resultJsonFactory ?: $this->_objectManager
            ->get(\Magento\Framework\Controller\Result\JsonFactory::class);
        $this->escaper = $escaper ?: $this->_objectManager
            ->get(\Magento\Framework\Escaper::class);
        $this->logger = $logger ?: $this->_objectManager
            ->get(\Psr\Log\LoggerInterface::class);
    }

    /**
     * Action for AJAX request
     *
     * @return void|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->_request->getParam('namespace') === null) {
            $this->_redirect('admin/noroute');
            return;
        }

        try {
            $component = $this->factory->create($this->_request->getParam('namespace'));
            if ($this->validateAclResource($component->getContext()->getDataProvider()->getConfigData())) {
                $this->prepareComponent($component);
                $this->_response->appendBody((string) $component->render());
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
            return $resultJson->setData($result);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $result = [
                'error' => _('UI component could not be rendered because of system exception'),
                'errorcode' => $this->escaper->escapeHtml($e->getCode())
            ];
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setStatusHeader(
                \Zend\Http\Response::STATUS_CODE_400,
                \Zend\Http\AbstractMessage::VERSION_11,
                'Bad Request'
            );
            return $resultJson->setData($result);
        }
    }

    /**
     * Call prepare method in the component UI
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
     * Optionally validate ACL resource of components with a DataSource/DataProvider
     *
     * @param mixed $dataProviderConfigData
     * @return bool
     */
    private function validateAclResource($dataProviderConfigData)
    {
        if (isset($dataProviderConfigData['aclResource'])) {
            if (!$this->_authorization->isAllowed($dataProviderConfigData['aclResource'])) {
                $this->_redirect('admin/denied');
                return false;
            }
        }

        return true;
    }
}
