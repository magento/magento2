<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Download;

use Magento\Catalog\Model\Product\Type\AbstractType\AbstractProductType;

class DownloadCustomOption extends \Magento\Framework\App\Action\Action
{
    /**
     * Custom options download action
     *
     * @return void
     */
    public function execute()
    {
        $quoteItemOptionId = $this->getRequest()->getParam('id');
        /** @var $option \Magento\Sales\Model\Quote\Item\Option */
        $option = $this->_objectManager->create('Magento\Sales\Model\Quote\Item\Option')->load($quoteItemOptionId);

        if (!$option->getId()) {
            $this->_forward('noroute');
            return;
        }

        $optionId = null;
        if (strpos($option->getCode(), AbstractProductType::OPTION_PREFIX) === 0) {
            $optionId = str_replace(AbstractProductType::OPTION_PREFIX, '', $option->getCode());
            if ((int)$optionId != $optionId) {
                $optionId = null;
            }
        }
        $productOption = null;
        if ($optionId) {
            /** @var $productOption \Magento\Catalog\Model\Product\Option */
            $productOption = $this->_objectManager->create('Magento\Catalog\Model\Product\Option')->load($optionId);
        }
        if (!$productOption ||
            !$productOption->getId() ||
            $productOption->getProductId() != $option->getProductId() ||
            $productOption->getType() != 'file'
        ) {
            $this->_forward('noroute');
            return;
        }

        try {
            $info = unserialize($option->getValue());
            if ($this->getRequest()->getParam('key') != $info['secret_key']) {
                $this->_forward('noroute');
                return;
            }
            $this->_download->downloadFile($info);
        } catch (\Exception $e) {
            $this->_forward('noroute');
        }
        exit(0);
    }
}
