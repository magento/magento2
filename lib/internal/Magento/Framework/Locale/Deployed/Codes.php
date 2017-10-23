<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale\Deployed;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Locale\AvailableLocalesInterface;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Framework\View\DesignInterface;

/**
 * Returns array of deployed locale codes for the theme.
 */
class Codes implements AvailableLocalesInterface
{
    /**
     * Works with file system.
     *
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * Factory for creating objects that implements \Magento\Framework\View\Design\ThemeInterface.
     *
     * @var FlyweightFactory
     */
    private $flyweightFactory;

    /**
     * @param FlyweightFactory $flyweightFactory factory for creating objects
     *        that implements \Magento\Framework\View\Design\ThemeInterface
     * @param Filesystem $fileSystem works with file system
     */
    public function __construct(
        FlyweightFactory $flyweightFactory,
        Filesystem $fileSystem
    ) {
        $this->fileSystem = $fileSystem;
        $this->flyweightFactory = $flyweightFactory;
    }

    /**
     * {@inheritdoc}
     *
     * If theme or file directory for theme static content does not exist then return an empty array.
     */
    public function getList($code, $area = DesignInterface::DEFAULT_AREA)
    {
        try {
            $theme = $this->flyweightFactory->create($code, $area);
            $reader = $this->fileSystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
            $dirs = $reader->read($theme->getFullPath());
        } catch (\Exception $e) {
            return [];
        }

        return array_map('basename', $dirs);
    }
}
