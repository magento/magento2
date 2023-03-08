<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller\Index;

use Exception;
use Laminas\Http\AbstractMessage;
use Laminas\Http\Response;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Model\UiComponentTypeResolver;
use Magento\Framework\Escaper;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;

/**
 * Is responsible for providing ui components information on store front.
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Render extends Action
{
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
        private readonly Context $context,
        private readonly UiComponentFactory $uiComponentFactory,
        private ?UiComponentTypeResolver $contentTypeResolver = null,
        private ?JsonFactory $resultJsonFactory = null,
        private ?Escaper $escaper = null,
        private ?LoggerInterface $logger = null
    ) {
        parent::__construct($context);
        $this->authorization = $context->getAuthorization();
        $this->contentTypeResolver = $contentTypeResolver
            ?? ObjectManager::getInstance()->get(UiComponentTypeResolver::class);
        $this->resultJsonFactory = $resultJsonFactory ?? ObjectManager::getInstance()->get(JsonFactory::class);
        $this->escaper = $escaper ?? ObjectManager::getInstance()->get(Escaper::class);
        $this->logger = $logger ?? ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Provides ui component
     *
     * @return ResponseInterface|Json|ResultInterface|void
     */
    public function execute()
    {
        if ($this->_request->getParam('namespace') === null) {
            return $this->_redirect('noroute');
        }
        try {
            $component = $this->uiComponentFactory->create($this->getRequest()->getParam('namespace'));
            if ($this->validateAclResource($component->getContext()->getDataProvider()->getConfigData())) {
                $this->prepareComponent($component);
                $this->getResponse()->appendBody((string)$component->render());

                $contentType = $this->contentTypeResolver->resolve($component->getContext());
                $this->getResponse()->setHeader('Content-Type', $contentType, true);
                return $this->getResponse();
            } else {
                /** @var ResultJson $resultJson */
                $resultJson = $this->resultJsonFactory->create();
                $resultJson->setStatusHeader(
                    Response::STATUS_CODE_403,
                    AbstractMessage::VERSION_11,
                    'Forbidden'
                );
                return $resultJson->setData(
                    [
                        'error' => $this->escaper->escapeHtml('Forbidden'),
                        'errorcode' => 403
                    ]
                );
            }
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            $result = [
                'error' => $this->escaper->escapeHtml($e->getMessage()),
                'errorcode' => $this->escaper->escapeHtml($e->getCode())
            ];
            /** @var ResultJson $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setStatusHeader(
                Response::STATUS_CODE_400,
                AbstractMessage::VERSION_11,
                'Bad Request'
            );

            return $resultJson->setData($result);
        } catch (Exception $e) {
            $this->logger->critical($e);
            $result = [
                'error' => __('UI component could not be rendered because of system exception'),
                'errorcode' => $this->escaper->escapeHtml($e->getCode())
            ];
            /** @var ResultJson $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setStatusHeader(
                Response::STATUS_CODE_400,
                AbstractMessage::VERSION_11,
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
