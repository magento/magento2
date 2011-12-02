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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Config_Fieldset extends Mage_Core_Model_Config_Base
{
    /**
     * Constructor.
     * Load configuration from enabled modules with appropriate caching.
     *
     * @param Varien_Simplexml_Element|string|null $data
     */
    public function __construct($data = null)
    {
        parent::__construct($data);

        $canUseCache = Mage::app()->useCache('config');
        if ($canUseCache) {
            /* Setup caching with no checksum validation */
            $this->setCache(Mage::app()->getCache())
                ->setCacheChecksum(null)
                ->setCacheId('fieldset_config')
                ->setCacheTags(array(Mage_Core_Model_Config::CACHE_TAG));
            if ($this->loadCache()) {
                return;
            }
        }

        $config = Mage::getConfig()->loadModulesConfiguration('fieldset.xml');
        $this->setXml($config->getNode());

        if ($canUseCache) {
            $this->saveCache();
        }
    }
}
