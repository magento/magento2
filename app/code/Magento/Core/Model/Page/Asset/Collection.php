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
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * List of page asset instances associated with unique identifiers
 */
namespace Magento\Core\Model\Page\Asset;

class Collection
{
    /**
     * @var \Magento\Core\Model\Page\Asset\AssetInterface[]
     */
    private $_assets = array();

    /**
     * Add an instance, identified by a unique identifier, to the list
     *
     * @param string $identifier
     * @param \Magento\Core\Model\Page\Asset\AssetInterface $asset
     */
    public function add($identifier, \Magento\Core\Model\Page\Asset\AssetInterface $asset)
    {
        $this->_assets[$identifier] = $asset;
    }

    /**
     * Whether an item belongs to a collection or not
     *
     * @param string $identifier
     * @return bool
     */
    public function has($identifier)
    {
        return isset($this->_assets[$identifier]);
    }

    /**
     * Remove an item from the list
     *
     * @param string $identifier
     */
    public function remove($identifier)
    {
        unset($this->_assets[$identifier]);
    }

    /**
     * Retrieve all items in the collection
     *
     * @return \Magento\Core\Model\Page\Asset\AssetInterface[]
     */
    public function getAll()
    {
        return $this->_assets;
    }
}
