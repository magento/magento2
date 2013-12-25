<?php
/**
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
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales controller for download purposes
 *
 * @category   Magento
 * @package    Magento_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Controller;

class Download extends \Magento\App\Action\Action
{
    /**
     * @var \Magento\App\Response\Http\FileFactory
     */
    protected $_fileResponseFactory;

    /**
     * Filesystem instance
     *
     * @var \Magento\Filesystem
     */
    protected $_filesystem;
    
    /**
     * @param \Magento\App\Action\Context $context
     * @param \Magento\App\Response\Http\FileFactory $fileResponseFactory
     * @param \Magento\Filesystem $filesystem
     */
    public function __construct(
        \Magento\App\Action\Context $context,
        \Magento\App\Response\Http\FileFactory $fileResponseFactory,
        \Magento\Filesystem $filesystem
    ) {
        $this->_fileResponseFactory = $fileResponseFactory;
        $this->_filesystem = $filesystem;
        parent::__construct($context);
    }

    /**
     * Custom options downloader
     *
     * @param mixed $info
     */
    protected function _downloadFileAction($info)
    {
        $secretKey = $this->getRequest()->getParam('key');
        try {
            if ($secretKey != $info['secret_key']) {
                throw new \Exception();
            }

            /** @var \Magento\Filesystem\Directory\Read $directory */
            $directory = $this->_objectManager->get('Magento\Filesystem')
                ->getDirectoryWrite(\Magento\Filesystem::ROOT);

            $relativePath = $info['order_path'];
            $filePath = $directory->getAbsolutePath($relativePath);
            if ((!$directory->isFile($relativePath) || !$directory->isReadable($relativePath))
                && !$this->_processDatabaseFile($filePath)
            ) {
                //try get file from quote
                $relativePath = $info['quote_path'];
                $filePath = $directory->getAbsolutePath($relativePath);
                if ((!$directory->isFile($relativePath) || !$directory->isReadable($relativePath))
                    && !$this->_processDatabaseFile($filePath)
                ) {
                    throw new \Exception();
                }
            }
            $this->_fileResponseFactory->create($info['title'], array(
               'value' => $filePath,
               'type'  => 'filename'
            ));
        } catch (\Exception $e) {
            $this->_forward('noroute');
        }
    }

    /**
     * Check file in database storage if needed and place it on file system
     *
     * @param string $filePath
     * @return bool
     */
    protected function _processDatabaseFile($filePath)
    {
        if (!$this->_objectManager->get('Magento\Core\Helper\File\Storage\Database')->checkDbUsage()) {
            return false;
        }

        $relativePath = $this->_objectManager->get('Magento\Core\Helper\File\Storage\Database')
            ->getMediaRelativePath($filePath);
        $file = $this->_objectManager->create('Magento\Core\Model\File\Storage\Database')
            ->loadByFilename($relativePath);

        if (!$file->getId()) {
            return false;
        }

        /** @var \Magento\Filesystem\Directory\WriteInterface $directory */
        $directory = $this->_objectManager->get('Magento\Filesystem')
            ->getDirectoryWrite(\Magento\Filesystem::ROOT);
        $stream = $directory->openFile($filePath, 'w+');
        $stream->lock();
        $stream->write($filePath, $file->getContent());
        $stream->unlock();
        $stream->close();

        return true;
    }

    /**
     * Profile custom options download action
     */
    public function downloadProfileCustomOptionAction()
    {
        $recurringProfile = $this->_objectManager->create('Magento\Sales\Model\Recurring\Profile')
            ->load($this->getRequest()->getParam('id'));

        if (!$recurringProfile->getId()) {
            $this->_forward('noroute');
        }

        $orderItemInfo = $recurringProfile->getData('order_item_info');
        try {
            $request = unserialize($orderItemInfo['info_buyRequest']);

            if ($request['product'] != $orderItemInfo['product_id']) {
                $this->_forward('noroute');
                return;
            }

            $optionId = $this->getRequest()->getParam('option_id');
            if (!isset($request['options'][$optionId])) {
                $this->_forward('noroute');
                return;
            }
            // Check if the product exists
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($request['product']);
            if (!$product || !$product->getId()) {
                $this->_forward('noroute');
                return;
            }
            // Try to load the option
            $option = $product->getOptionById($optionId);
            if (!$option || !$option->getId() || $option->getType() != 'file') {
                $this->_forward('noroute');
                return;
            }
            $this->_downloadFileAction($request['options'][$this->getRequest()->getParam('option_id')]);
        } catch (\Exception $e) {
            $this->_forward('noroute');
        }
    }

    /**
     * Custom options download action
     */
    public function downloadCustomOptionAction()
    {
        $quoteItemOptionId = $this->getRequest()->getParam('id');
        /** @var $option \Magento\Sales\Model\Quote\Item\Option */
        $option = $this->_objectManager->create('Magento\Sales\Model\Quote\Item\Option')->load($quoteItemOptionId);

        if (!$option->getId()) {
            $this->_forward('noroute');
            return;
        }

        $optionId = null;
        if (strpos($option->getCode(), \Magento\Catalog\Model\Product\Type\AbstractType::OPTION_PREFIX) === 0) {
            $optionId = str_replace(\Magento\Catalog\Model\Product\Type\AbstractType::OPTION_PREFIX, '', $option->getCode());
            if ((int)$optionId != $optionId) {
                $optionId = null;
            }
        }
        $productOption = null;
        if ($optionId) {
            /** @var $productOption \Magento\Catalog\Model\Product\Option */
            $productOption = $this->_objectManager->create('Magento\Catalog\Model\Product\Option')->load($optionId);
        }
        if (!$productOption || !$productOption->getId()
            || $productOption->getProductId() != $option->getProductId() || $productOption->getType() != 'file'
        ) {
            $this->_forward('noroute');
            return;
        }

        try {
            $info = unserialize($option->getValue());
            $this->_downloadFileAction($info);
        } catch (\Exception $e) {
            $this->_forward('noroute');
        }
        exit(0);
    }
}
