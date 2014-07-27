<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\RecurringPayment\Controller\Download;

class DownloadPaymentCustomOption extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Sales\Model\Download
     */
    protected $download;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Sales\Model\Download $download
     */
    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Sales\Model\Download $download)
    {
        parent::__construct($context);
        $this->download = $download;
    }

    /**
     * Retrieve custom option information
     *
     * @param array $buyRequest
     * @return array
     * @throws \Exception
     */
    protected function getOptionInfo($buyRequest)
    {
        $optionId = $this->getRequest()->getParam('option_id');
        if (!isset($buyRequest['options'][$optionId])) {
            throw new \Exception();
        }
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($buyRequest['product']);
        if (!$product->getId()) {
            throw new \Exception();
        }
        $option = $product->getOptionById($optionId);
        if (!$option || !$option->getId() || $option->getType() != 'file') {
            throw new \Exception();
        }
        $info = $buyRequest['options'][$this->getRequest()->getParam('option_id')];
        if ($this->getRequest()->getParam('key') != $info['secret_key']) {
            throw new \Exception();
        }
        return $info;
    }

    /**
     * Payment custom options download action
     *
     * @return void
     */
    public function execute()
    {
        $recurringPayment = $this->_objectManager->create(
            'Magento\RecurringPayment\Model\Payment'
        )->load(
            $this->getRequest()->getParam('id')
        );

        if (!$recurringPayment->getId()) {
            $this->_forward('noroute');
        }

        $orderItemInfo = $recurringPayment->getData('order_item_info');
        try {
            $buyRequest = unserialize($orderItemInfo['info_buyRequest']);
            if ($buyRequest['product'] != $orderItemInfo['product_id']) {
                throw new \Exception();
            }
            $this->download->downloadFile($this->getOptionInfo($buyRequest));
        } catch (\Exception $e) {
            $this->_forward('noroute');
        }
    }
}
