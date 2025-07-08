<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Controller\Adminhtml\PageCache;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class ExportVarnishConfig action which exports vcl config file
 */
class ExportVarnishConfig extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Magento_Backend::system';

    /**
     * @var \Magento\Backend\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\PageCache\Model\Config
     */
    protected $config;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\PageCache\Model\Config $config
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
     */
    public function execute()
    {
        $fileName = 'varnish.vcl';
        $varnishVersion = $this->getRequest()->getParam('varnish');
        switch ($varnishVersion) {
            case 7:
                $content = $this->config->getVclFile(\Magento\PageCache\Model\Config::VARNISH_7_CONFIGURATION_PATH);
                break;
            case 6:
                $content = $this->config->getVclFile(\Magento\PageCache\Model\Config::VARNISH_6_CONFIGURATION_PATH);
                break;
            default:
                $content = $this->config->getVclFile(\Magento\PageCache\Model\Config::VARNISH_6_CONFIGURATION_PATH);
                break;
        }
        return $this->fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
