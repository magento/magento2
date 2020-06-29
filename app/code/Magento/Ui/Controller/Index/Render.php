<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Ui\Controller\Index;

use Laminas\Http\AbstractMessage;
use Laminas\Http\Response;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Model\UiComponentTypeResolver;
use Psr\Log\LoggerInterface;

/**
 * Is responsible for providing ui components information on store front.
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Render extends Action implements HttpGetActionInterface
{
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
        $this->uiComponentFactory = $uiComponentFactory;
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
        if ($this->getRequest()->getParam('namespace') === null) {
            $this->redirect('admin/noroute');

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
                /** @var Json $resultJson */
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
            /** @var Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setStatusHeader(
                Response::STATUS_CODE_400,
                AbstractMessage::VERSION_11,
                'Bad Request'
            );

            return $resultJson->setData($result);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $result = [
                'error' => __('UI component could not be rendered because of system exception'),
                'errorcode' => $this->escaper->escapeHtml($e->getCode())
            ];
            /** @var Json $resultJson */
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
     * Set redirect into response
     *
     * @param string $path
     * @param array $arguments
     * @return ResponseInterface
     */
    private function redirect($path, $arguments = []): ResponseInterface
    {
        $this->_redirect->redirect($this->getResponse(), $path, $arguments);
        return $this->getResponse();
    }

    /**
     * Optionally validate ACL resource of components with a DataSource/DataProvider
     *
     * @param mixed $dataProviderConfigData
     * @return boolean
     */
    private function validateAclResource($dataProviderConfigData): bool
    {
        if (isset($dataProviderConfigData['aclResource'])) {
            if (!$this->authorization->isAllowed($dataProviderConfigData['aclResource'])) {
                if (!$this->getRequest()->isAjax()) {
                    $this->redirect('noroute');
                }

                return false;
            }
        }

        return true;
    }

    /**
     * Call prepare method in the component UI
     *
     * @param UiComponentInterface $component
     * @return void
     */
    private function prepareComponent(UiComponentInterface $component): void
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareComponent($child);
        }
        $component->prepare();
    }
}
