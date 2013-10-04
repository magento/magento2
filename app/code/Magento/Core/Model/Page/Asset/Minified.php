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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Minified page asset
 */
namespace Magento\Core\Model\Page\Asset;

class Minified implements \Magento\Core\Model\Page\Asset\MergeableInterface
{

    /**
     * @var \Magento\Core\Model\Page\Asset\LocalInterface
     */
    protected $_originalAsset;

    /**
     * @var \Magento\Code\Minifier
     */
    protected $_minifier;

    /**
     * @var string
     */
    protected $_file;

    /**
     * @var string
     */
    protected $_url;

    /**
     * @var \Magento\Core\Model\View\Url
     */
    protected $_viewUrl;

    /**
     * @var \Magento\Core\Model\Logger
     */
    protected $_logger;

    /**
     * @param \Magento\Core\Model\Page\Asset\LocalInterface $asset
     * @param \Magento\Code\Minifier $minifier
     * @param \Magento\Core\Model\View\Url $viewUrl
     * @param \Magento\Core\Model\Logger $logger
     */
    public function __construct(
        \Magento\Core\Model\Page\Asset\LocalInterface $asset,
        \Magento\Code\Minifier $minifier,
        \Magento\Core\Model\View\Url $viewUrl,
        \Magento\Core\Model\Logger $logger
    ) {
        $this->_originalAsset = $asset;
        $this->_minifier = $minifier;
        $this->_viewUrl = $viewUrl;
        $this->_logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        if (empty($this->_url)) {
            $this->_process();
        }
        return $this->_url;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return $this->_originalAsset->getContentType();
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceFile()
    {
        if (empty($this->_file)) {
            $this->_process();
        }
        return $this->_file;
    }

    /**
     * Minify content of child asset
     */
    protected function _process()
    {
        $originalFile = $this->_originalAsset->getSourceFile();

        try {
            $this->_file = $this->_minifier->getMinifiedFile($originalFile);
        } catch (\Exception $e) {
            $this->_logger->logException(new \Magento\Exception('Could not minify file: ' . $originalFile, 0, $e));
            $this->_file = $originalFile;
        }
        if ($this->_file == $originalFile) {
            $this->_url = $this->_originalAsset->getUrl();
        } else {
            $this->_url = $this->_viewUrl->getPublicFileUrl($this->_file);
        }
    }
}
