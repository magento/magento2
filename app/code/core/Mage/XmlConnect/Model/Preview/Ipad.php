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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Ipad preview model
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_Preview_Ipad extends Mage_XmlConnect_Model_Preview_Abstract
{
    /**
     * Current device orientation
     *
     * @var string
     */
    protected $_orientation = 'unknown';

    /**
     * Set device orientation
     *
     * @param string $orientation
     * @return Mage_XmlConnect_Model_Preview_Ipad
     */
    public function setOrientation($orientation)
    {
        $this->_orientation = $orientation;
        return $this;
    }

    /**
     * Get current device orientation
     *
     * @return string
     */
    public function getOrientation()
    {
        return $this->_orientation;
    }

    /**
     * Get application banner image url
     *
     * @return string
     */
    public function getBannerImage()
    {
        $orientation = $this->getOrientation();
        switch ($orientation) {
            case Mage_XmlConnect_Helper_Ipad::ORIENTATION_LANDSCAPE:
                $configPath = 'conf/body/bannerIpadLandscapeImage';
                $imageUrlOrig = $this->getData($configPath);
                if ($imageUrlOrig) {
                    $width  = Mage_XmlConnect_Helper_Ipad::PREVIEW_LANDSCAPE_BANNER_WIDTH;
                    $height = Mage_XmlConnect_Helper_Ipad::PREVIEW_LANDSCAPE_BANNER_HEIGHT;
                    $bannerImage = Mage::helper('Mage_XmlConnect_Helper_Image')
                        ->getCustomSizeImageUrl($imageUrlOrig, $width, $height);
                } else {
                    $bannerImage = $this->getPreviewImagesUrl('ipad/banner_image_l.png');
                }
                break;
            case Mage_XmlConnect_Helper_Ipad::ORIENTATION_PORTRAIT:
                $configPath = 'conf/body/bannerIpadImage';
                $imageUrlOrig = $this->getData($configPath);
                if ($imageUrlOrig) {
                    $width  = Mage_XmlConnect_Helper_Ipad::PREVIEW_PORTRAIT_BANNER_WIDTH;
                    $height = Mage_XmlConnect_Helper_Ipad::PREVIEW_PORTRAIT_BANNER_HEIGHT;
                    $bannerImage = Mage::helper('Mage_XmlConnect_Helper_Image')
                        ->getCustomSizeImageUrl($imageUrlOrig, $width, $height);
                } else {
                    $bannerImage = $this->getPreviewImagesUrl('ipad/banner_image.png');
                }
                break;
        }
        return $bannerImage;
    }

    /**
     * Get background image url according orientation
     *
     * @throws Mage_Core_Exception
     * @return string
     */
    public function getBackgroundImage()
    {
        $orientation = $this->getOrientation();
        $backgroundImage = '';
        /** @var $helperImage Mage_XmlConnect_Helper_Image */
        $helperImage = Mage::helper('Mage_XmlConnect_Helper_Image');

        switch ($orientation) {
            case Mage_XmlConnect_Helper_Ipad::ORIENTATION_LANDSCAPE:
                $configPath = 'conf/body/backgroundIpadLandscapeImage';
                $imageUrlOrig = $this->getData($configPath);
                if ($imageUrlOrig) {
                    $width = Mage_XmlConnect_Helper_Ipad::PREVIEW_LANDSCAPE_BACKGROUND_WIDTH;
                    $height = Mage_XmlConnect_Helper_Ipad::PREVIEW_LANDSCAPE_BACKGROUND_HEIGHT;
                    $backgroundImage = $helperImage->getCustomSizeImageUrl($imageUrlOrig, $width, $height);
                } else {
                    $backgroundImage = $this->getPreviewImagesUrl('ipad/background_home_landscape.jpg');
                }
                break;
            case Mage_XmlConnect_Helper_Ipad::ORIENTATION_PORTRAIT:
                $configPath = 'conf/body/backgroundIpadPortraitImage';
                $imageUrlOrig = $this->getData($configPath);
                if ($imageUrlOrig) {
                    $width = Mage_XmlConnect_Helper_Ipad::PREVIEW_PORTRAIT_BACKGROUND_WIDTH;
                    $height = Mage_XmlConnect_Helper_Ipad::PREVIEW_PORTRAIT_BACKGROUND_HEIGHT;
                    $backgroundImage = $helperImage->getCustomSizeImageUrl($imageUrlOrig, $width, $height);
                } else {
                    $backgroundImage = $this->getPreviewImagesUrl('ipad/background_portrait.jpg');
                }
                break;
            default:
                Mage::throwException(
                    Mage::helper('Mage_XmlConnect_Helper_Data')->__('Wrong Ipad background image orientation has been specified: "%s".', $orientation)
                );
                break;
        }
        return $backgroundImage;
    }
}
