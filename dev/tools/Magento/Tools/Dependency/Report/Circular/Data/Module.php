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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\Dependency\Report\Circular\Data;

/**
 * Module
 */
class Module
{
    /**
     * Module name
     *
     * @var string
     */
    protected $name;

    /**
     * Circular dependencies chains
     *
     * @var \Magento\Tools\Dependency\Report\Circular\Data\Chain[]
     */
    protected $chains;

    /**
     * Module construct
     *
     * @param array $name
     * @param \Magento\Tools\Dependency\Report\Circular\Data\Chain[] $chains
     */
    public function __construct($name, array $chains = array())
    {
        $this->name = $name;
        $this->chains = $chains;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get circular dependencies chains
     *
     * @return \Magento\Tools\Dependency\Report\Circular\Data\Chain[]
     */
    public function getChains()
    {
        return $this->chains;
    }

    /**
     * Get circular dependencies chains count
     *
     * @return int
     */
    public function getChainsCount()
    {
        return count($this->chains);
    }
}
