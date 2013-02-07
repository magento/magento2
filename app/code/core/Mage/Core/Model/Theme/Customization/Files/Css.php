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
     * Css file name
     */
    const FILE_PATH = 'css/custom.css';

    /**
     * Css file type customization
     */
    const TYPE = 'css_file';

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
        return Mage_Core_Model_Theme_Files::TYPE_CSS;
    }

    /**
     * Save data
     *
     * @param $theme Mage_Core_Model_Theme
     * @return Mage_Core_Model_Theme_Customization_Files_Css
     */
    protected function _save($theme)
    {
        /** @var $cssFile Mage_Core_Model_Theme_Files */
        $cssFile = $this->getCollectionByTheme($theme)->getFirstItem();
        $cssFile->addData(array(
            'theme_id'  => $theme->getId(),
            'file_path' => self::FILE_PATH,
            'file_type' => $this->_getFileType(),
            'content'   => $this->_dataForSave
        ))->save();

        return $this;
    }
}
