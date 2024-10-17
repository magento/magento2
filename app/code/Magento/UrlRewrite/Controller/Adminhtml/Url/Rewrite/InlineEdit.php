<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteFactory as UrlRewriteFactoryAlias;
use Magento\UrlRewrite\Model\UrlRewrite;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use RuntimeException;

/**
 * Inline edit action class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEdit extends Rewrite implements HttpPostActionInterface
{
    /**
     * @param Context $context
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param UrlRewriteFactoryAlias $urlRewriteResourceFactory
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        private readonly UrlRewriteFactory $urlRewriteFactory,
        private readonly UrlRewriteFactoryAlias $urlRewriteResourceFactory,
        private readonly JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
    }

    /**
     * Inline edit save action
     *
     * @return Json
     */
    public function execute(): Json
    {
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam(
            'items',
            []
        );
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData(
                [
                    'messages' => [__('Please correct the data sent.')],
                    'error' => true,
                ]
            );
        }

        $urlRewriteResource = $this->urlRewriteResourceFactory->create();
        foreach (array_keys($postItems) as $urlRewriteId) {
            $urlRewrite = $this->urlRewriteFactory->create();
            $urlRewriteResource->load($urlRewrite, $urlRewriteId);

            try {
                $urlRewrite->addData($postItems[$urlRewriteId]);
                $urlRewriteResource->save($urlRewrite);
            } catch (RuntimeException $e) {
                $messages[] = $this->getErrorWithUrlRewriteId(
                    $urlRewrite,
                    $e->getMessage()
                );
                $error = true;
            } catch (Exception $e) {
                $messages[] = $this->getErrorWithUrlRewriteId(
                    $urlRewrite,
                    __('Something went wrong while saving the url rewrite.')
                );
                $error = true;
            }
        }

        return $resultJson->setData(
            [
                'messages' => $messages,
                'error' => $error
            ]
        );
    }

    /**
     * Get error message for url rewrite
     *
     * @param UrlRewrite $urlRewrite
     * @param string $errorText
     * @return string
     */
    private function getErrorWithUrlRewriteId(UrlRewrite $urlRewrite, $errorText): string
    {
        return '[Url rewrite ID: ' . $urlRewrite->getId() . '] ' . $errorText;
    }
}
