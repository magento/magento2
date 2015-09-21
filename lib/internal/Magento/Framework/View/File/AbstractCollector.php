<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\File;

use Magento\Framework\Filesystem;
use Magento\Framework\View\File\Factory as FileFactory;
use Magento\Framework\View\Helper\PathPattern as PathPatternHelper;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Abstract file collector
 */
abstract class AbstractCollector implements CollectorInterface
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $directory;

    /**
     * @var \Magento\Framework\View\File\Factory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\View\Helper\PathPattern
     */
    protected $pathPatternHelper;

    /**
     * @var string
     */
    protected $subDir;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\View\File\Factory $fileFactory
     * @param \Magento\Framework\View\Helper\PathPattern $pathPatternHelper
     * @param string $subDir
     */
    public function __construct(
        Filesystem $filesystem,
        FileFactory $fileFactory,
        PathPatternHelper $pathPatternHelper,
        $subDir = ''
    ) {
        $this->directory = $filesystem->getDirectoryRead($this->getScopeDirectory());
        $this->fileFactory = $fileFactory;
        $this->pathPatternHelper = $pathPatternHelper;
        $this->subDir = $subDir ? $subDir . '/' : '';
    }

    /**
     * Get scope directory of this file collector
     *
     * @return string
     */
    protected function getScopeDirectory()
    {
        return DirectoryList::MODULES;
    }
}
