<?php
/**
 * List of parent classes with their parents and interfaces
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_ObjectManager_Relations implements Magento_ObjectManager_Relations
{
    /**
     * Relations file
     *
     * @var string
     */
    protected $_filePath;

    /**
     * List of class relations
     *
     * @var array
     */
    protected $_relations;

    /**
     * @param Mage_Core_Model_Dir $dirs
     */
    public function __construct(Mage_Core_Model_Dir $dirs)
    {
        $this->_filePath = $dirs->getDir(Mage_Core_Model_Dir::DI) . DIRECTORY_SEPARATOR . 'relations.php';
    }

    /**
     * Serialize relations data
     *
     * @return array
     */
    public function __sleep()
    {
        return array();
    }

    /**
     * Retrieve parents for class
     *
     * @param string $type
     * @return array
     */
    public function getParents($type)
    {
        if (!$this->_relations) {
            $this->_relations = unserialize(file_get_contents($this->_filePath));
        }
        return isset($this->_relations[$type]) ? $this->_relations[$type] : array();
    }
}
