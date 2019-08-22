<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Model\UiComponentTypeResolver;
use Magento\Framework\Escaper;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\AuthorizationInterface;

/**
 * Is responsible for providing ui components information on store front.
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Render extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var UiComponentFactory
     */
    private $uiComponentFactory;

    /**
     * @var UiComponentTypeResolver
     */
    private $contentTypeResolver;

    /**
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
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * Render constructor.
     * @param Context $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UiComponentTypeResolver|null $contentTypeResolver
     * @param JsonFactory|null $resultJsonFactory
     * @param Escaper|null $escaper
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Context $context,
        UiComponentFactory $uiComponentFactory,
        ?UiComponentTypeResolver $contentTypeResolver = null,
        JsonFactory $resultJsonFactory = null,
        Escaper $escaper = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->uiComponentFactory = $uiComponentFactory;
        $this->authorization = $context->getAuthorization();
        $this->contentTypeResolver = $contentTypeResolver
            ?? ObjectManager::getInstance()->get(UiComponentTypeResolver::class);
        $this->resultJsonFactory = $resultJsonFactory ?? ObjectManager::getInstance()->get(JsonFactory::class);
        $this->escaper = $escaper ?? ObjectManager::getInstance()->get(Escaper::class);
        $this->logger = $logger ?? ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if ($this->_request->getParam('namespace') === null) {
            $this->_redirect('admin/noroute');

            return;
        }

        try {
            $component = $this->uiComponentFactory->create($this->getRequest()->getParam('namespace'));
            if ($this->validateAclResource($component->getContext()->getDataProvider()->getConfigData())) {
                $this->prepareComponent($component);
                $this->getResponse()->appendBody((string)$component->render());

                $contentType = $this->contentTypeResolver->resolve($component->getContext());
                $this->getResponse()->setHeader('Content-Type', $contentType, true);
            } else {
                /** @var \Magento\Framework\Controller\Result\Json $resultJson */
                $resultJson = $this->resultJsonFactory->create();
                $resultJson->setStatusHeader(
                    \Zend\Http\Response::STATUS_CODE_403,
                    \Zend\Http\AbstractMessage::VERSION_11,
                    'Forbidden'
                );
                return $resultJson->setData(
                    [
                        'error' => $this->escaper->escapeHtml('Forbidden'),
                        'errorcode' => 403
                    ]
                );
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

            return $resultJson->setData($result);
        }
    }

    /**
     * Call prepare method in the component UI
     *
     * @param UiComponentInterface $component
     * @return void
     */
    private function prepareComponent(UiComponentInterface $component)
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
            if (!$this->authorization->isAllowed($dataProviderConfigData['aclResource'])) {
                if (!$this->_request->isAjax()) {
                    $this->_redirect('noroute');
                }

                return false;
            }
        }

        return true;
    }
}
