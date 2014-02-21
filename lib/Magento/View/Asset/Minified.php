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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\View\Asset;

/**
 * Minified page asset
 */
class Minified implements MergeableInterface
{
    /**
     * LocalInterface
     *
     * @var LocalInterface
     */
    protected $originalAsset;

    /**
     * Minfier
     *
     * @var \Magento\Code\Minifier
     */
    protected $minifier;

    /**
     * File
     *
     * @var string
     */
    protected $file;

    /**
     * URL
     *
     * @var string
     */
    protected $url;

    /**
     * View URL
     *
     * @var \Magento\View\Url
     */
    protected $viewUrl;

    /**
     * Logger
     *
     * @var \Magento\Logger
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param LocalInterface $asset
     * @param \Magento\Code\Minifier $minifier
     * @param \Magento\View\Url $viewUrl
     * @param \Magento\Logger $logger
     */
    public function __construct(
        LocalInterface $asset,
        \Magento\Code\Minifier $minifier,
        \Magento\View\Url $viewUrl,
        \Magento\Logger $logger
    ) {
        $this->originalAsset = $asset;
        $this->minifier = $minifier;
        $this->viewUrl = $viewUrl;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        if (empty($this->url)) {
            $this->process();
        }
        return $this->url;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return $this->originalAsset->getContentType();
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceFile()
    {
        if (empty($this->file)) {
            $this->process();
        }
        return $this->file;
    }

    /**
     * Minify content of child asset
     *
     * @return void
     */
    protected function process()
    {
        $originalFile = $this->originalAsset->getSourceFile();

        try {
            $this->file = $this->minifier->getMinifiedFile($originalFile);
        } catch (\Exception $e) {
            $this->logger->logException(new \Magento\Exception('Could not minify file: ' . $originalFile, 0, $e));
            $this->file = $originalFile;
        }
        if ($this->file == $originalFile) {
            $this->url = $this->originalAsset->getUrl();
        } else {
            $this->url = $this->viewUrl->getPublicFileUrl($this->file);
        }
    }
}
