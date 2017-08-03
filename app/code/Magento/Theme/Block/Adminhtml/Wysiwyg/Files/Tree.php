<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Adminhtml\Wysiwyg\Files;

/**
 * Files tree block
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Tree extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Theme\Helper\Storage
     * @since 2.0.0
     */
    protected $_storageHelper;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     * @since 2.0.0
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Theme\Helper\Storage $storageHelper
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @throws \RuntimeException
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Theme\Helper\Storage $storageHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->_storageHelper = $storageHelper;
        $this->urlEncoder = $urlEncoder;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        parent::__construct($context, $data);
    }

    /**
     * Json source URL
     *
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getTreeJson($data)
    {
        return $this->serializer->serialize($data);
    }

    /**
     * Get root node name of tree
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getRootNodeName()
    {
        return __('Storage Root');
    }

    /**
     * Return tree node full path based on current path
     *
     * @return string
     * @since 2.0.0
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
