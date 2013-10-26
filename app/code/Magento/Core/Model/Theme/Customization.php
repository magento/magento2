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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme customizations manager
 */
namespace Magento\Core\Model\Theme;

class Customization implements \Magento\Core\Model\Theme\CustomizationInterface
{
    /**
     * @var \Magento\Core\Model\Resource\Theme\File\CollectionFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Core\Model\Theme\Customization\Path
     */
    protected $_customizationPath;

    /**
     * @var \Magento\Core\Model\Theme
     */
    protected $_theme;

    /**
     * @var \Magento\Core\Model\Resource\Theme\File\Collection
     */
    protected $_themeFiles;

    /**
     * @var \Magento\Core\Model\Resource\Theme\File\Collection[]
     */
    protected $_themeFilesByType = array();

    /**
     * @param \Magento\Core\Model\Resource\Theme\File\CollectionFactory $fileFactory
     * @param \Magento\Core\Model\Theme\Customization\Path $customizationPath
     * @param \Magento\View\Design\ThemeInterface $theme
     */
    public function __construct(
        \Magento\Core\Model\Resource\Theme\File\CollectionFactory $fileFactory,
        \Magento\Core\Model\Theme\Customization\Path $customizationPath,
        \Magento\View\Design\ThemeInterface $theme = null
    ) {
        $this->_fileFactory = $fileFactory;
        $this->_customizationPath = $customizationPath;
        $this->_theme = $theme;
    }

    /**
     * Retrieve list of files which belong to a theme
     *
     * @return \Magento\Core\Model\Theme\FileInterface[]
     */
    public function getFiles()
    {
        if (!$this->_themeFiles) {
            $this->_themeFiles = $this->_fileFactory->create();
            $this->_themeFiles->addThemeFilter($this->_theme);
            $this->_themeFiles->setDefaultOrder();
        }
        return $this->_themeFiles->getItems();
    }

    /**
     * Retrieve list of files which belong to a theme only by type
     *
     * @param string $type
     * @return \Magento\Core\Model\Theme\FileInterface[]
     */
    public function getFilesByType($type)
    {
        if (!isset($this->_themeFilesByType[$type])) {
            $themeFiles = $this->_fileFactory->create();
            $themeFiles->addThemeFilter($this->_theme);
            $themeFiles->addFieldToFilter('file_type', $type);
            $themeFiles->setDefaultOrder();
            $this->_themeFilesByType[$type] = $themeFiles;
        }
        return $this->_themeFilesByType[$type]->getItems();
    }

    /**
     * Get short file information
     *
     * @param \Magento\Core\Model\Theme\FileInterface[] $files
     * @return array
     */
    public function generateFileInfo(array $files)
    {
        $filesInfo = array();
        /** @var $file \Magento\Core\Model\Theme\FileInterface */
        foreach ($files as $file) {
            if ($file instanceof \Magento\Core\Model\Theme\FileInterface) {
                $filesInfo[] = $file->getFileInfo();
            }
        }
        return $filesInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomizationPath()
    {
        return $this->_customizationPath->getCustomizationPath($this->_theme);
    }

    /**
     * {@inheritdoc}
     */
    public function getThemeFilesPath()
    {
        return $this->_theme->isPhysical()
            ? $this->_customizationPath->getThemeFilesPath($this->_theme)
            : $this->_customizationPath->getCustomizationPath($this->_theme);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomViewConfigPath()
    {
        return $this->_customizationPath->getCustomViewConfigPath($this->_theme);
    }

    /**
     * Reorder files positions
     *
     * @param string $type
     * @param array $sequence
     * @return $this
     */
    public function reorder($type, array $sequence)
    {
        $sortOrderSequence = array_flip(array_values($sequence));
        /** @var $file \Magento\Core\Model\Theme\FileInterface */
        foreach ($this->getFilesByType($type) as $file) {
            if (isset($sortOrderSequence[$file->getId()])) {
                $prevSortOrder = $file->getData('sort_order');
                $currentSortOrder = $sortOrderSequence[$file->getId()];
                if ($prevSortOrder !== $currentSortOrder) {
                    $file->setData('sort_order', $currentSortOrder);
                    $file->save();
                }
            }
        }
        return $this;
    }

    /**
     * Remove custom files by ids
     *
     * @param array $fileIds
     * @return $this
     */
    public function delete(array $fileIds)
    {
        /** @var $file \Magento\Core\Model\Theme\FileInterface */
        foreach ($this->getFiles() as $file) {
            if (in_array($file->getId(), $fileIds)) {
                $file->delete();
            }
        }
        return $this;
    }
}
