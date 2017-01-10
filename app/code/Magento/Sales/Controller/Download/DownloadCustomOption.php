<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Controller\Download;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Controller\Result\ForwardFactory;

/**
 * Class DownloadCustomOption
 * @package Magento\Sales\Controller\Download
 */
class DownloadCustomOption extends \Magento\Framework\App\Action\Action
{
    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Sales\Model\Download
     */
    protected $download;

    /**
     * @var \Magento\Framework\Unserialize\Unserialize
     * @deprecated
     */
    protected $unserialize;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param \Magento\Sales\Model\Download $download
     * @param \Magento\Framework\Unserialize\Unserialize $unserialize
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        \Magento\Sales\Model\Download $download,
        \Magento\Framework\Unserialize\Unserialize $unserialize,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->download = $download;
        $this->unserialize = $unserialize;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Serialize\Serializer\Json::class
        );
    }

    /**
     * Custom options download action
     *
     * @return void|\Magento\Framework\Controller\Result\Forward
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $quoteItemOptionId = $this->getRequest()->getParam('id');
        /** @var $option \Magento\Quote\Model\Quote\Item\Option */
        $option = $this->_objectManager->create(
            \Magento\Quote\Model\Quote\Item\Option::class
        )->load($quoteItemOptionId);
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();

        if (!$option->getId()) {
            return $resultForward->forward('noroute');
        }

        $optionId = null;
        if (strpos($option->getCode(), AbstractType::OPTION_PREFIX) === 0) {
            $optionId = str_replace(AbstractType::OPTION_PREFIX, '', $option->getCode());
            if ((int)$optionId != $optionId) {
                $optionId = null;
            }
        }
        $productOption = null;
        if ($optionId) {
            /** @var $productOption \Magento\Catalog\Model\Product\Option */
            $productOption = $this->_objectManager->create(
                \Magento\Catalog\Model\Product\Option::class
            )->load($optionId);
        }

        if (!$productOption || !$productOption->getId() || $productOption->getType() != 'file') {
            return $resultForward->forward('noroute');
        }

        try {
            $info = $this->serializer->unserialize($option->getValue());
            if ($this->getRequest()->getParam('key') != $info['secret_key']) {
                return $resultForward->forward('noroute');
            }
            $this->download->downloadFile($info);
        } catch (\Exception $e) {
            return $resultForward->forward('noroute');
        }
        $this->endExecute();
    }

    /**
     * Ends execution process
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    protected function endExecute()
    {
        exit(0);
    }
}
