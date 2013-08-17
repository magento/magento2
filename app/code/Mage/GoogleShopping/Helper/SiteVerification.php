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
 * @package     Mage_GoogleShopping
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Google Site Verification Helper
 */
class Mage_GoogleShopping_Helper_SiteVerification extends Mage_Core_Helper_Abstract
{
    /**
     * Name meta data for Google Site Verification
     */
    const META_NAME = 'google-site-verification';

    /**
     * @var Mage_GoogleShopping_Model_Config
     */
    protected $_config;

    /**
     * @param Mage_Core_Helper_Context $context
     * @param Mage_GoogleShopping_Model_Config $config
     */
    public function __construct(Mage_Core_Helper_Context $context, Mage_GoogleShopping_Model_Config $config)
    {
        $this->_config = $config;
        parent::__construct($context);
    }

    /**
     * Get meta for site verification
     *
     * @param int $storeId
     * @return array
     */
    public function getMetaTag($storeId = null)
    {
        return array(
            'name'    => self::META_NAME,
            'content' => $this->_config->getConfigData('verify_meta_tag', $storeId)
        );
    }
}
