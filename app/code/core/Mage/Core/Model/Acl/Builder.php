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
 * Access Control List Builder. Retrieves required role/rule/resource loaders from configuration and uses them
 * to populate provided ACL object. If loaders are not defined - default loader is used that does not do anything
 * to ACL
 */
class Mage_Core_Model_Acl_Builder
{
    /**
     * Acl object
     *
     * @var Magento_Acl
     */
    protected $_acl;

    /**
     * Area configuration
     *
     * @var Varien_Simplexml_Element
     */
    protected $_config;

    /**
     * Application config object
     *
     * @var Mage_Core_Model_Config
     */
    protected $_objectFactory;

    /**
     * @param array $data
     * @throws InvalidArgumentException
     */
    public function __construct(array $data = array())
    {
        if (!isset($data['areaConfig'])) {
            throw new InvalidArgumentException('Area Config must be passed to ACL builder');
        }
        $this->_areaConfig = $data['areaConfig'];
        if (!isset($data['objectFactory'])) {
            throw new InvalidArgumentException('Object Factory must be passed to ACL builder');
        }
        $this->_objectFactory = $data['objectFactory'];
    }

    /**
     * Build Access Control List
     *
     * @return Magento_Acl
     * @throws LogicException
     */
    public function getAcl()
    {
        if (!$this->_acl) {
            try {
                $acl = $this->_objectFactory->getModelInstance('Magento_Acl');
                $this->_objectFactory->getModelInstance($this->_getLoaderClass('resource'))->populateAcl($acl);
                $this->_objectFactory->getModelInstance($this->_getLoaderClass('role'))->populateAcl($acl);
                $this->_objectFactory->getModelInstance($this->_getLoaderClass('rule'))->populateAcl($acl);
                $this->_acl = $acl;
            } catch (Exception $e) {
                throw new LogicException('Could not create acl object: ' . $e->getMessage());
            }
        }
        return $this->_acl;
    }

    /**
     * Retrieve ACL loader class from config or NullLoader if not defined
     *
     * @param string $loaderType
     * @return string
     */
    protected function _getLoaderClass($loaderType)
    {
        $loaderClass = (string) (isset($this->_areaConfig['acl'][$loaderType . 'Loader'])
            ? $this->_areaConfig['acl'][$loaderType . 'Loader']
            : '');

        return $loaderClass ?: 'Magento_Acl_Loader_Default';
    }
}
