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
class Mage_Core_Model_Theme_Customization_Files_Css extends Mage_Core_Model_Theme_Customization_Files_FilesAbstract
{
    /**
     * Custom css type
     */
    const CUSTOM_CSS = 'custom';

    /**
     * Quick style css type
     */
    const QUICK_STYLE_CSS = 'quick_style';

    /**
     * Css file type customization
     */
    const TYPE = 'css_file';

    /**
     * Css files by type
     *
     * @var array
     */
    protected $_cssFiles = array(
        self::CUSTOM_CSS      => 'css/custom.css',
        self::QUICK_STYLE_CSS => 'css/quick_style.css'
    );

    /**
     * Return css file customization type
     *
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * Return file type
     *
     * @return string
     */
    protected function _getFileType()
    {
        return Mage_Core_Model_Theme_File::TYPE_CSS;
    }

    /**
     * Get CSS file name by type
     *
     * @param string $type
     * @return string
     * @throws InvalidArgumentException
     */
    public function getFilePathByType($type)
    {
        if (!array_key_exists($type, $this->_cssFiles)) {
            throw new InvalidArgumentException('Invalid CSS file type');
        }
        return $this->_cssFiles[$type];
    }

    /**
     * Save data
     *
     * @param $theme Mage_Core_Model_Theme
     * @return Mage_Core_Model_Theme_Customization_Files_Css
     */
    protected function _save($theme)
    {
        foreach ($this->_dataForSave as $type => $cssFileContent) {
            /** @var $cssFiles Mage_Core_Model_Theme_File */
            $cssFile = $this->getCollectionByTheme($theme, $type)->getFirstItem();

            $cssFile->addData(array(
                'theme_id'  => $theme->getId(),
                'file_path' => $this->getFilePathByType($type),
                'file_type' => $this->_getFileType(),
                'content'   => $cssFileContent
            ))->save();
        }

        return $this;
    }

    /**
     * Get theme collection
     *
     * @param Mage_Core_Model_Theme_Customization_CustomizedInterface $theme
     * @param null|string $type
     * @return Mage_Core_Model_Resource_Theme_File_Collection
     */
    public function getCollectionByTheme(
        Mage_Core_Model_Theme_Customization_CustomizedInterface $theme,
        $type = Mage_Core_Model_Theme_Customization_Files_Css::CUSTOM_CSS
    ) {
        return (null === $type)
            ? parent::getCollectionByTheme($theme)
            : parent::getCollectionByTheme($theme)->addFilter('file_path', $this->getFilePathByType($type));
    }
}
