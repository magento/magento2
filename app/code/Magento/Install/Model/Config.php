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
 * @category    Magento
 * @package     Magento_Install
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Install config
 *
 * @category   Magento
 * @package    Magento_Install
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Install\Model;

class Config
{

    /**
     * Config data model
     *
     * @var  \Magento\Install\Model\Config\Data
     */
    protected $_dataStorage;

    /**
     * Directory model
     *
     * @var \Magento\App\Dir
     */
    protected $_coreDir;



    /**
     * @param \Magento\Install\Model\Config\Data $dataStorage
     * @param \Magento\App\Dir $coreDir
     */
    public function __construct(\Magento\Install\Model\Config\Data $dataStorage, \Magento\App\Dir $coreDir)
    {
        $this->_dataStorage = $dataStorage;
        $this->_coreDir = $coreDir;
    }

    /**
     * Get array of wizard steps
     *
     * array($index => \Magento\Object)
     *
     * @return array
     */
    public function getWizardSteps()
    {
        $data = $this->_dataStorage->get();
        $steps = array();
        foreach ($data['steps'] as $step) {
            $stepObject = new \Magento\Object($step);
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
     * @deprecated since 1.7.1.0
     *
     * @return array
     */
    public function getPathForCheck()
    {
        $data = $this->_dataStorage->get();
        $res = array();

        $items = (isset($data['filesystem_prerequisites'])
            && isset($data['filesystem_prerequisites']['writables'])) ?
            $data['filesystem_prerequisites']['writables'] : array();

        foreach ($items as $item) {
            $res['writeable'][] = $item;
        }

        return $res;
    }

    /**
     * Retrieve writable full paths for checking
     *
     * @return array
     */
    public function getWritableFullPathsForCheck()
    {
        $data = $this->_dataStorage->get();
        $paths = array();
        $items = (isset($data['filesystem_prerequisites'])
            && isset($data['filesystem_prerequisites']['writables'])) ?
            $data['filesystem_prerequisites']['writables'] : array();
        foreach ($items as $nodeKey => $item) {
            $value = $item;
            $value['path'] = $this->_coreDir->getDir($nodeKey);
            $paths[$nodeKey] = $value;
        }

        return $paths;
    }
}
