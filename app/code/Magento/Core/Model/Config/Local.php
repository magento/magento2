<?php
/**
 * Magento application object manager. Configures and application application
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
namespace Magento\Core\Model\Config;

class Local
{
    /**
     * Config data
     *
     * @var array
     */
    protected $_data;

    /**
     * DI configuration
     *
     * @var array
     */
    protected $_configuration = array();

    /**
     * @var \Magento\Core\Model\Config\Loader\Local
     */
    protected $_loader;

    /**
     * @param \Magento\Core\Model\Config\Loader\Local $loader
     */
    public function __construct(\Magento\Core\Model\Config\Loader\Local $loader)
    {
        $this->_loader = $loader;
        $this->_data = $loader->load();
    }

    /**
     * @return array
     */
    public function getParams()
    {
        $stack = $this->_data;
        unset($stack['resource']);
        unset($stack['connection']);
        $separator = '.';
        $parameters = array();

        while ($stack) {
            list($key, $value) = each($stack);
            unset($stack[$key]);
            if (is_array($value)) {
                if (count($value)) {
                    foreach ($value as $subKey => $node) {
                        $build[$key . $separator . $subKey] = $node;
                    }
                } else {
                    $build[$key] = null;
                }
                $stack = $build + $stack;
                continue;
            }
            $parameters[$key] = $value;
        }
        return $parameters;
    }

    /**
     * Retrieve connection configuration by connection name
     *
     * @param string $connectionName
     * @return array
     */
    public function getConnection($connectionName)
    {
        return isset($this->_data['connection'][$connectionName])
            ? $this->_data['connection'][$connectionName]
            : null;
    }

    /**
     * Retrieve list of connections
     *
     * @return array
     */
    public function getConnections()
    {
        return isset($this->_data['connection']) ? $this->_data['connection'] : array();
    }

    /**
     * Retrieve resources
     *
     * @return array
     */
    public function getResources()
    {
        return isset($this->_data['resource']) ? $this->_data['resource'] : array();
    }

    /**
     * Reload local.xml
     */
    public function reload()
    {
        $this->_data = $this->_loader->load();
    }
}
