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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\ObjectManager\Relations;

class Compiled implements \Magento\Framework\ObjectManager\Relations
{
    /**
     * List of class relations
     *
     * @var array
     */
    protected $_relations;

    /**
     * Default relation list
     *
     * @var array
     */
    protected $_default = array();

    /**
     * @param array $relations
     */
    public function __construct(array $relations)
    {
        $this->_relations = $relations;
    }

    /**
     * Check whether requested type is available for read
     *
     * @param string $type
     * @return bool
     */
    public function has($type)
    {
        return isset($this->_relations[$type]);
    }

    /**
     * Retrieve parents for class
     *
     * @param string $type
     * @return array
     */
    public function getParents($type)
    {
        return $this->_relations[$type];
    }
}
