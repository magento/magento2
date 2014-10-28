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
namespace Magento\Framework\View\Asset;

/**
 * List of page asset instances associated with unique identifiers
 */
class Collection
{
    /**
     * Assets
     *
     * @var AssetInterface[]
     */
    protected $assets = array();

    /**
     * Add an instance, identified by a unique identifier, to the list
     *
     * @param string $identifier
     * @param AssetInterface $asset
     * @return void
     */
    public function add($identifier, AssetInterface $asset)
    {
        $this->assets[$identifier] = $asset;
    }

    /**
     * Whether an item belongs to a collection or not
     *
     * @param string $identifier
     * @return bool
     */
    public function has($identifier)
    {
        return isset($this->assets[$identifier]);
    }

    /**
     * Remove an item from the list
     *
     * @param string $identifier
     * @return void
     */
    public function remove($identifier)
    {
        unset($this->assets[$identifier]);
    }

    /**
     * Retrieve all items in the collection
     *
     * @return AssetInterface[]
     */
    public function getAll()
    {
        return $this->assets;
    }
}
