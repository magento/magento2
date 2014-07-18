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
namespace Magento\Wishlist\Controller\Index;

use Magento\Wishlist\Controller\IndexInterface;
use Magento\Framework\App\Action;

class DownloadCustomOption extends Action\Action implements IndexInterface
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileResponseFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileResponseFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileResponseFactory
    ) {
        $this->_fileResponseFactory = $fileResponseFactory;
        parent::__construct($context);
    }

    /**
     * Custom options download action
     *
     * @return void
     */
    public function execute()
    {
        $option = $this->_objectManager->create(
            'Magento\Wishlist\Model\Item\Option'
        )->load(
            $this->getRequest()->getParam('id')
        );

        if (!$option->getId()) {
            return $this->_forward('noroute');
        }

        $optionId = null;
        if (strpos($option->getCode(), \Magento\Catalog\Model\Product\Type\AbstractType::OPTION_PREFIX) === 0) {
            $optionId = str_replace(
                \Magento\Catalog\Model\Product\Type\AbstractType::OPTION_PREFIX,
                '',
                $option->getCode()
            );
            if ((int)$optionId != $optionId) {
                return $this->_forward('noroute');
            }
        }
        $productOption = $this->_objectManager->create('Magento\Catalog\Model\Product\Option')->load($optionId);

        if (!$productOption ||
            !$productOption->getId() ||
            $productOption->getProductId() != $option->getProductId() ||
            $productOption->getType() != 'file'
        ) {
            return $this->_forward('noroute');
        }

        try {
            $info = unserialize($option->getValue());
            $filePath = $this->_objectManager->get(
                'Magento\Framework\App\Filesystem'
            )->getPath(
                \Magento\Framework\App\Filesystem::ROOT_DIR
            ) . $info['quote_path'];
            $secretKey = $this->getRequest()->getParam('key');

            if ($secretKey == $info['secret_key']) {
                $this->_fileResponseFactory->create(
                    $info['title'],
                    array('value' => $filePath, 'type' => 'filename'),
                    \Magento\Framework\App\Filesystem::ROOT_DIR
                );
            }
        } catch (\Exception $e) {
            $this->_forward('noroute');
        }
        exit(0);
    }
}
