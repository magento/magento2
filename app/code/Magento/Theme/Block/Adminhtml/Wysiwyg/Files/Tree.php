<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Adminhtml\Wysiwyg\Files;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\EncoderInterface;
use Magento\Theme\Helper\Storage;
use RuntimeException;

/**
 * Files tree block
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Tree extends Template
{
    /**
     * @var Storage
     */
    protected $_storageHelper;

    /**
     * @param Context $context
     * @param Storage $storageHelper
     * @param EncoderInterface $urlEncoder
     * @param array $data
     * @param Json|null $serializer
     * @throws RuntimeException
     */
    public function __construct(
        Context $context,
        Storage $storageHelper,
        protected readonly EncoderInterface $urlEncoder,
        array $data = [],
        private ?Json $serializer = null
    ) {
        $this->_storageHelper = $storageHelper;
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(Json::class);
        parent::__construct($context, $data);
    }

    /**
     * Json source URL
     *
     * @return string
     */
    public function getTreeLoaderUrl()
    {
        return $this->getUrl('adminhtml/*/treeJson', $this->_storageHelper->getRequestParams());
    }

    /**
     * Get tree json
     *
     * @param array $data
     * @return string
     */
    public function getTreeJson($data)
    {
        return $this->serializer->serialize($data);
    }

    /**
     * Get root node name of tree
     *
     * @return Phrase
     */
    public function getRootNodeName()
    {
        return __('Storage Root');
    }

    /**
     * Return tree node full path based on current path
     *
     * @return string
     */
    public function getTreeCurrentPath()
    {
        $treePath = '/root';
        $path = $this->_storageHelper->getSession()->getCurrentPath();
        if ($path) {
            $path = str_replace($this->_storageHelper->getStorageRoot(), '', $path);
            $relative = '';
            foreach (explode('/', $path) as $dirName) {
                if ($dirName) {
                    $relative .= '/' . $dirName;
                    $treePath .= '/' . $this->urlEncoder->encode($relative);
                }
            }
        }
        return $treePath;
    }
}
