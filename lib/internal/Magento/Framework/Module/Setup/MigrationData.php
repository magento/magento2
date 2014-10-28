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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Module\Setup;

/**
 * Replace patterns needed for migration process between Magento versions
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MigrationData
{
    /**
     * List of required params
     *
     * @var string[]
     */
    protected $_requiredParams = array('plain', 'wiki', 'xml', 'serialized');

    /**
     * List of replace patterns
     *
     * @var string[]
     */
    protected $_patterns = array();

    /**
     * @param array $data
     * @throws \InvalidArgumentException
     */
    public function __construct(
        array $data
    ) {
        foreach ($this->_requiredParams as $param) {
            if (!isset($data[$param])) {
                throw new \InvalidArgumentException("Missing required param " . $param);
            }
            $this->_patterns[$param] = $data[$param];
        }
    }

    /**
     * Get replace pattern
     *
     * @return string
     */
    public function getPlainFindPattern()
    {
        return $this->_patterns['plain'];
    }

    /**
     * Get replace pattern
     *
     * @return string
     */
    public function getWikiFindPattern()
    {
        return $this->_patterns['wiki'];
    }

    /**
     * Get replace pattern
     *
     * @return string
     */
    public function getXmlFindPattern()
    {
        return $this->_patterns['xml'];
    }

    /**
     * Get replace pattern
     *
     * @return string
     */
    public function getSerializedFindPattern()
    {
        return $this->_patterns['serialized'];
    }
}
