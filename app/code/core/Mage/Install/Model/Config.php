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
 * @package     Mage_Install
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Install config
 *
 * @category   Mage
 * @package    Mage_Install
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Install_Model_Config extends Varien_Simplexml_Config
{

    const XML_PATH_WIZARD_STEPS     = 'wizard/steps';
    const XML_PATH_CHECK_WRITEABLE  = 'check/filesystem/writeable';
    const XML_PATH_CHECK_EXTENSIONS = 'check/php/extensions';

    public function __construct()
    {
        parent::__construct();
        $this->loadString('<?xml version="1.0"?><config></config>');
        Mage::getConfig()->loadModulesConfiguration('install.xml', $this);
    }

    /**
     * Get array of wizard steps
     *
     * array($inndex => Varien_Object )
     *
     * @return array
     */
    public function getWizardSteps()
    {
        $steps = array();
        foreach ((array)$this->getNode(self::XML_PATH_WIZARD_STEPS) as $stepName => $step) {
            $stepObject = new Varien_Object((array)$step);
            $stepObject->setName($stepName);
            $steps[] = $stepObject;
        }
        return $steps;
    }

    /**
     * Retrieve writable path for checking
     *
     * array(
     *      ['writeable'] => array(
     *          [$index] => array(
     *              ['path']
     *              ['recursive']
     *          )
     *      )
     * )
     *
     * @return array
     */
    public function getPathForCheck()
    {
        $res = array();

        $items = (array) $this->getNode(self::XML_PATH_CHECK_WRITEABLE);

        foreach ($items as $item) {
            $res['writeable'][] = (array) $item;
        }

        return $res;
    }

    /**
     * Retrieve required PHP extensions
     *
     * @return array
     */
    public function getExtensionsForCheck()
    {
        $res = array();
        $items = (array) $this->getNode(self::XML_PATH_CHECK_EXTENSIONS);

        foreach ($items as $name => $value) {
            if (!empty($value)) {
                $res[$name] = array();
                foreach ($value as $subname => $subvalue) {
                    $res[$name][] = $subname;
                }
            }
            else {
                $res[$name] = (array) $value;
            }
        }

        return $res;
    }

}
