<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Controller\Adminhtml\PageCache;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class \Magento\PageCache\Controller\Adminhtml\PageCache\ExportVarnishConfig
 *
 * @since 2.0.0
 */
class ExportVarnishConfig extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\App\Response\Http\FileFactory
     * @since 2.0.0
     */
    protected $fileFactory;

    /**
     * @var \Magento\PageCache\Model\Config
     * @since 2.0.0
     */
    protected $config;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\PageCache\Model\Config $config
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\PageCache\Model\Config $config
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->fileFactory = $fileFactory;
    }

    /**
     * Export Varnish Configuration as .vcl
     *
     * @return \Magento\Framework\App\ResponseInterface
     * @since 2.0.0
     */
    public function execute()
    {
        $fileName = 'varnish.vcl';
        $varnishVersion = $this->getRequest()->getParam('varnish');
        switch ($varnishVersion) {
            case 5:
                $content = $this->config->getVclFile(\Magento\PageCache\Model\Config::VARNISH_5_CONFIGURATION_PATH);
                break;
            default:
                $content = $this->config->getVclFile(\Magento\PageCache\Model\Config::VARNISH_4_CONFIGURATION_PATH);
                break;
        }
        return $this->fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
