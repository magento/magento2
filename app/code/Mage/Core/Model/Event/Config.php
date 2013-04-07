<?php
/**
 * Event configuration model
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Event_Config
{
    /**
     * Modules configuration model
     *
     * @var Mage_Core_Model_Config_Modules
     */
    protected $_config;

    /**
     * Configuration for events by area
     *
     * @var array
     */
    protected $_eventAreas = array();

    /**
     * @param Mage_Core_Model_Config_Modules $config
     */
    public function __construct(Mage_Core_Model_Config_Modules $config)
    {
        $this->_config = $config;
    }

    /**
     * Get area events configuration
     *
     * @param   string $area event area
     * @return  Mage_Core_Model_Config_Element
     */
    protected function _getAreaEvent($area)
    {
        if (!isset($this->_eventAreas[$area])) {
            $this->_eventAreas[$area] = $this->_config->getNode($area)->events;
        }
        return $this->_eventAreas[$area];
    }

    /**
     * Populate event manager with area event observers
     *
     * @param Mage_Core_Model_Event_Manager $eventManager
     * @param $area
     */
    public function populate(Mage_Core_Model_Event_Manager $eventManager, $area)
    {
        $areaConfig = $this->_getAreaEvent($area);
        if (!$areaConfig) {
            return;
        }

        foreach($areaConfig->children() as $eventName => $eventConfig) {
            $observers = array();
            $eventObservers = $eventConfig->observers->children();
            if (!$eventObservers) {
                $eventManager->addObservers($area, $eventName, $observers);
                continue;
            }

            foreach ($eventObservers as $obsName => $obsConfig) {
                $observers[$obsName] = array(
                    'type'  => (string)$obsConfig->type,
                    'model' => $obsConfig->class ? (string) $obsConfig->class : $obsConfig->getClassName(),
                    'method'=> (string)$obsConfig->method,
                );
            }
            $eventManager->addObservers($area, $eventName, $observers);
        }
    }
}
