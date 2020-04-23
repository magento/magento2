<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Controller\Index;

use Magento\Framework\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class DownloadCustomOption. Represents request-flow logic for option's file download
 */
class DownloadCustomOption extends \Magento\Wishlist\Controller\AbstractIndex implements HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileResponseFactory;

    /**
     * Json Serializer Instance
     *
     * @var Json
     */
    private $json;

    /**
     * Constructor method
     *
     * @param Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileResponseFactory
     * @param Json|null $json
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileResponseFactory,
        Json $json = null
    ) {
        $this->_fileResponseFactory = $fileResponseFactory;
        $this->json = $json ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct($context);
    }

    /**
     * Custom options download action
     *
     * @return \Magento\Framework\Controller\Result\Forward
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $option = $this->_objectManager->create(
            \Magento\Wishlist\Model\Item\Option::class
        )->load(
            $this->getRequest()->getParam('id')
        );
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        if (!$option->getId()) {
            $resultForward->forward('noroute');
            return $resultForward;
        }

        $optionId = null;
        if (strpos($option->getCode(), \Magento\Catalog\Model\Product\Type\AbstractType::OPTION_PREFIX) === 0) {
            $optionId = str_replace(
                \Magento\Catalog\Model\Product\Type\AbstractType::OPTION_PREFIX,
                '',
                $option->getCode()
            );
            if ((int)$optionId != $optionId) {
                $resultForward->forward('noroute');
                return $resultForward;
            }
        }
        $productOption = $this->_objectManager->create(\Magento\Catalog\Model\Product\Option::class)->load($optionId);

        if (!$productOption ||
            !$productOption->getId() ||
            $productOption->getProductId() != $option->getProductId() ||
            $productOption->getType() != 'file'
        ) {
            $resultForward->forward('noroute');
            return $resultForward;
        }

        try {
            $info = $this->json->unserialize($option->getValue());
            $secretKey = $this->getRequest()->getParam('key');

            if ($secretKey == $info['secret_key']) {
                $this->_fileResponseFactory->create(
                    $info['title'],
                    ['value' => $info['quote_path'], 'type' => 'filename'],
                    DirectoryList::MEDIA,
                    $info['type']
                );
            }
        } catch (\Exception $e) {
            $resultForward->forward('noroute');
            return $resultForward;
        }
    }
}
