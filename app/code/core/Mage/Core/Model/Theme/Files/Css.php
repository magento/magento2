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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme css file model class
 */
class Mage_Core_Model_Theme_Files_Css
{
    /**
     * Css file name
     */
    const FILE_NAME = 'custom.css';

    /**
     * @var Mage_Core_Model_Theme_Files
     */
    protected $_themeFiles;

    /**
     * @param Mage_Core_Model_Theme_Files $themeFiles
     */
    public function __construct(Mage_Core_Model_Theme_Files $themeFiles)
    {
        $this->_themeFiles = $themeFiles;
    }

    /**
     * Save data from form
     *
     * @param $theme Mage_Core_Model_Theme
     * @param string $themeCssData
     * @return Mage_Core_Model_Theme_Files
     */
    public function saveFormData($theme, $themeCssData)
    {
        /** @var $cssModel Mage_Core_Model_Theme_Files */
        $cssFile = $this->getFileByTheme($theme);
        $cssFile->addData(array(
            'theme_id'  => $theme->getId(),
            'file_name' => self::FILE_NAME,
            'file_type' => Mage_Core_Model_Theme_Files::TYPE_CSS,
            'content'   => $themeCssData
        ))->save();
        return $cssFile;
    }

    /**
     * Return theme css file by theme
     *
     * @param $theme Mage_Core_Model_Theme
     * @return Mage_Core_Model_Theme_Files
     */
    public function getFileByTheme($theme)
    {
        /** @var $cssModel Mage_Core_Model_Theme_Files */
        $cssFile = $this->_themeFiles->getCollection()
            ->addFilter('theme_id', $theme->getId())
            ->addFilter('file_type', Mage_Core_Model_Theme_Files::TYPE_CSS)
            ->getFirstItem();

        return $cssFile;
    }
}
