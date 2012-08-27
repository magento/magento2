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
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Visual design editor adapter abstract
 *
 * @method string getName()
 * @method string getHandle()
 * @method string getType()
 * @method array getActions()
 */
abstract class Mage_DesignEditor_Model_History_Manager_Adapter_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * Add action to element
     *
     * @abstract
     * @param string $action
     * @param array $data
     * @return Mage_DesignEditor_Model_History_Manager_Adapter_Abstract
     */
    abstract public function addAction($action, $data);

    /**
     * Render element data
     *
     * @abstract
     * @return mixed
     */
    abstract public function render();

    /**
     * Convert element to history log
     *
     * @return array
     */
    public function toHistoryLog()
    {
        $resultData = array();

        foreach ($this->getActions() as $action => $data) {
            $resultData[] = array(
                'handle'       => $this->getHandle(),
                'change_type'  => $this->getType(),
                'element_name' => $this->getName(),
                'action_name'  => $action,
                'action_data'  => $data,
            );
        }

        return $resultData;
    }
}
