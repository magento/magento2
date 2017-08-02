<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Theme controller
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design;

/**
 * Class \Magento\Theme\Controller\Adminhtml\System\Design\Theme
 *
 * @since 2.0.0
 */
abstract class Theme extends \Magento\Backend\App\Action
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
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     * @since 2.0.0
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     * @since 2.0.0
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Framework\Filesystem
     * @since 2.0.0
     */
    protected $_appFileSystem;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Filesystem $appFileSystem
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Filesystem $appFileSystem
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_fileFactory = $fileFactory;
        $this->_assetRepo = $assetRepo;
        $this->_appFileSystem = $appFileSystem;
        parent::__construct($context);
    }
}
