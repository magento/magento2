<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Locale;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\AvailableLocalesInterface;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\Filesystem;

/**
 * Returns the list of deployed locales for the theme.
 */
class DeployedLocales implements AvailableLocalesInterface
{
    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var FlyweightFactory
     */
    private $flyweightFactory;

    /**
     * @param FlyweightFactory $flyweightFactory
     * @param Filesystem $fileSystem
     */
    public function __construct(
        FlyweightFactory $flyweightFactory,
        FileSystem $fileSystem
    ) {
        $this->fileSystem = $fileSystem;
        $this->flyweightFactory = $flyweightFactory;
    }

    /**
     * {@inheritdoc}
     *
     * If theme or theme file directory with static content does not exist then return an empty array.
     */
    public function getList($code, $area = DesignInterface::DEFAULT_AREA)
    {
        try {
            $theme = $this->flyweightFactory->create($code, $area);
            $reader = $this->fileSystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
            $dirs = $reader->read($theme->getFullPath());
        } catch (LocalizedException $e) {
            return [];
        }

        return array_map('basename', $dirs);
    }
}
