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
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class Mage_Backend_Model_Menu_DirectorAbstract
{
    /**
     * Configuration data
     * @var
     */
    protected $_configModel;

    /**
     * Factory model
     * @var Mage_Core_Model_Config
     */
    protected $_factory;

    /**
     * @param array $data
     * @throws InvalidArgumentException if config storage is not present in $data array
     */
    public function __construct(array $data = array())
    {
        if (isset($data['config'])) {
            $this->_configModel = $data['config'];
        } else {
            throw new InvalidArgumentException('Configuration storage model is required parameter');
        }

        if (isset($data['factory'])) {//} && $data['factory'] instanceof Mage_Core_Model_Config) {
            $this->_factory = $data['factory'];
        } else {
            throw new InvalidArgumentException('Configuration factory model is required parameter');
        }
    }

    /**
     * Apply menu commands to builder object
     * @abstract
     * @param  Mage_Backend_Model_Menu_Builder $builder
     * @return Mage_Backend_Model_Menu_DirectorAbstract
     */
    abstract public function buildMenu(Mage_Backend_Model_Menu_Builder $builder);
}
