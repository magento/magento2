<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Model\Editor\Tools\Controls;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Controls configuration factory
 */
class Factory
{
    /**#@+
     * Group of types
     */
    const TYPE_QUICK_STYLES = 'quick-style';

    const TYPE_IMAGE_SIZING = 'image-sizing';

    /**#@-*/

    /**
     * File names with
     *
     * @var array
     */
    protected $_fileNames = [
        self::TYPE_QUICK_STYLES => 'Magento_DesignEditor::controls/quick_styles.xml',
        self::TYPE_IMAGE_SIZING => 'Magento_DesignEditor::controls/image_sizing.xml',
    ];

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @var \Magento\Framework\Config\FileIteratorFactory
     */
    protected $fileIteratorFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Config\FileIteratorFactory $fileIteratorFactory
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Config\FileIteratorFactory $fileIteratorFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->_objectManager = $objectManager;
        $this->assetRepo = $assetRepo;
        $this->fileIteratorFactory = $fileIteratorFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * Get file path by type
     *
     * @param string $type
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return string
     * @throws \Magento\Framework\Exception
     */
    protected function _getFilePathByType($type, $theme)
    {
        if (!isset($this->_fileNames[$type])) {
            throw new \Magento\Framework\Exception("Unknown control configuration type: \"{$type}\"");
        }
        return $this->assetRepo->createAsset(
            $this->_fileNames[$type],
            ['area' => \Magento\Framework\View\DesignInterface::DEFAULT_AREA, 'themeModel' => $theme]
        )
        ->getSourceFile();
    }

    /**
     * Create new instance
     *
     * @param string $type
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param \Magento\Framework\View\Design\ThemeInterface $parentTheme
     * @param string[] $files
     * @return \Magento\DesignEditor\Model\Editor\Tools\Controls\Configuration
     * @throws \Magento\Framework\Exception
     */
    public function create(
        $type,
        \Magento\Framework\View\Design\ThemeInterface $theme = null,
        \Magento\Framework\View\Design\ThemeInterface $parentTheme = null,
        array $files = []
    ) {
        $files[] = $this->_getFilePathByType($type, $theme);
        switch ($type) {
            case self::TYPE_QUICK_STYLES:
                $class = 'Magento\DesignEditor\Model\Config\Control\QuickStyles';
                break;
            case self::TYPE_IMAGE_SIZING:
                $class = 'Magento\DesignEditor\Model\Config\Control\ImageSizing';
                break;
            default:
                throw new \Magento\Framework\Exception("Unknown control configuration type: \"{$type}\"");
        }
        $rootDirectory = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
        $paths = [];
        foreach ($files as $file) {
            $paths[] = $rootDirectory->getRelativePath($file);
        }
        $fileIterator = $this->fileIteratorFactory->create($rootDirectory, $paths);
        /** @var $config \Magento\DesignEditor\Model\Config\Control\AbstractControl */
        $config = $this->_objectManager->create($class, ['configFiles' => $fileIterator]);

        return $this->_objectManager->create(
            'Magento\DesignEditor\Model\Editor\Tools\Controls\Configuration',
            ['configuration' => $config, 'theme' => $theme, 'parentTheme' => $parentTheme]
        );
    }
}
