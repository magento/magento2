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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Iphone preview model
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_Preview_Iphone extends Mage_XmlConnect_Model_Preview_Abstract
{
    /**
     * Get application banner image url
     *
     * @throws Mage_Core_Exception
     * @return string
     */
    public function getBannerImage()
    {
        $configPath = 'conf/body/bannerImage';
        if ($this->getData($configPath)) {
            $bannerImage = $this->getData($configPath);
        } else {
            $bannerImage = $this->getPreviewImagesUrl('banner.png');
        }
        return $bannerImage;
    }

    /**
     * Get background image url according device type
     *
     * @return string
     */
    public function getBackgroundImage()
    {
        $configPath = 'conf/body/backgroundImage';
        $imageUrlOrig = $this->getData($configPath);
        if ($imageUrlOrig) {
            $backgroundImage = $imageUrlOrig;
        } else {
            $backgroundImage = $this->getPreviewImagesUrl('background.png');
        }
        return $backgroundImage;
    }
}
