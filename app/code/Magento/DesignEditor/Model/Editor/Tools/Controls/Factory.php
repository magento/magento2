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
 * @package     Magento_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Controls configuration factory
 */
namespace Magento\DesignEditor\Model\Editor\Tools\Controls;

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
    protected $_fileNames = array(
        self::TYPE_QUICK_STYLES => 'Magento_DesignEditor::controls/quick_styles.xml',
        self::TYPE_IMAGE_SIZING => 'Magento_DesignEditor::controls/image_sizing.xml'
    );

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Core\Model\View\FileSystem
     */
    protected $_viewFileSystem;

    /**
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\Core\Model\View\FileSystem $viewFileSystem
     */
    public function __construct(
        \Magento\ObjectManager $objectManager,
        \Magento\Core\Model\View\FileSystem $viewFileSystem
    ) {
        $this->_objectManager = $objectManager;
        $this->_viewFileSystem = $viewFileSystem;
    }

    /**
     * Get file path by type
     *
     * @param string $type
     * @param \Magento\View\Design\ThemeInterface $theme
     * @return string
     * @throws \Magento\Exception
     */
    protected function _getFilePathByType($type, $theme)
    {
        if (!isset($this->_fileNames[$type])) {
            throw new \Magento\Exception("Unknown control configuration type: \"{$type}\"");
        }
        return $this->_viewFileSystem->getFilename($this->_fileNames[$type], array(
            'area'       => \Magento\View\DesignInterface::DEFAULT_AREA,
            'themeModel' => $theme
        ));
    }

    /**
     * Create new instance
     *
     * @param string $type
     * @param \Magento\View\Design\ThemeInterface $theme
     * @param \Magento\View\Design\ThemeInterface $parentTheme
     * @param array $files
     * @return \Magento\DesignEditor\Model\Editor\Tools\Controls\Configuration
     * @throws \Magento\Exception
     */
    public function create(
        $type,
        \Magento\View\Design\ThemeInterface $theme = null,
        \Magento\View\Design\ThemeInterface $parentTheme = null,
        array $files = array()
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
                throw new \Magento\Exception("Unknown control configuration type: \"{$type}\"");
                break;
        }
        /** @var $config \Magento\DesignEditor\Model\Config\Control\AbstractControl */
        $config = $this->_objectManager->create($class, array('configFiles' => $files));
        return $this->_objectManager->create(
            'Magento\DesignEditor\Model\Editor\Tools\Controls\Configuration', array(
                'configuration' => $config,
                'theme'         => $theme,
                'parentTheme'   => $parentTheme
        ));
    }
}
