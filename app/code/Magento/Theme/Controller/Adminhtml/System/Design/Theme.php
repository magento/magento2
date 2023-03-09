<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Theme controller
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory as ResponseHttpFileFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\View\Asset\Repository as AssetRepository;

abstract class Theme extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Theme::theme';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var ResponseHttpFileFactory
     */
    protected $_fileFactory;

    /**
     * @var AssetRepository
     */
    protected $_assetRepo;

    /**
     * @var Filesystem
     */
    protected $_appFileSystem;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param ResponseHttpFileFactory $fileFactory
     * @param AssetRepository $assetRepo
     * @param Filesystem $appFileSystem
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ResponseHttpFileFactory $fileFactory,
        AssetRepository $assetRepo,
        Filesystem $appFileSystem
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_fileFactory = $fileFactory;
        $this->_assetRepo = $assetRepo;
        $this->_appFileSystem = $appFileSystem;
        parent::__construct($context);
    }
}
