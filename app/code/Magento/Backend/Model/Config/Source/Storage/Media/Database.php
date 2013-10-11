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
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Generate options for media database selection
 */
namespace Magento\Backend\Model\Config\Source\Storage\Media;

class Database implements \Magento\Core\Model\Option\ArrayInterface
{
    /**
     * @var \Magento\Core\Model\Config\Local
     */
    protected $_config;

    /**
     * @param \Magento\Core\Model\Config\Local
     */
    public function __construct(\Magento\Core\Model\Config\Local $config)
    {
        $this->_config = $config;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $connectionOptions = array();
        foreach (array_keys($this->_config->getConnections()) as $connectionName) {
            $connectionOptions[] = array('value' => $connectionName, 'label' => $connectionName);
        }
        sort($connectionOptions);
        reset($connectionOptions);
        return $connectionOptions;
    }
}
