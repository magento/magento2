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
namespace Magento\Framework\View\Layout;

/**
 * Interface ProcessorInterface
 */
interface ProcessorInterface
{
    /**
     * Add XML update instruction
     *
     * @param string $update
     * @return ProcessorInterface
     */
    public function addUpdate($update);

    /**
     * Get all registered updates as array
     *
     * @return array
     */
    public function asArray();

    /**
     * Get all registered updates as string
     *
     * @return string
     */
    public function asString();

    /**
     * Add handle(s) to update
     *
     * @param string|string[] $handleName
     * @return ProcessorInterface
     */
    public function addHandle($handleName);

    /**
     * Remove handle from update
     *
     * @param string $handleName
     * @return ProcessorInterface
     */
    public function removeHandle($handleName);

    /**
     * Get handle names array
     *
     * @return array
     */
    public function getHandles();

    /**
     * Add page handles
     *
     * Add the first existing (declared in layout updates) page handle along with all parents to the update.
     * Return whether any page handles have been added or not.
     *
     * @param array $handlesToTry
     * @return bool
     */
    public function addPageHandles(array $handlesToTry);

    /**
     * Retrieve all design abstractions that exist in the system.
     *
     * @return array
     */
    public function getAllDesignAbstractions();

    /**
     * Check page_layout design abstractions that exist in the system
     *
     * @param array $abstraction
     * @return bool
     */
    public function isPageLayoutDesignAbstraction(array $abstraction);

    /**
     * Check custom design abstractions that exist in the system
     *
     * @param array $abstraction
     * @return bool
     */
    public function isCustomerDesignAbstraction(array $abstraction);

    /**
     * Load layout updates by handles
     *
     * @param array|string $handles
     * @throws \Magento\Framework\Exception
     * @return ProcessorInterface
     */
    public function load($handles = array());

    /**
     * Get layout updates as \Magento\Framework\View\Layout\Element object
     *
     * @return \SimpleXMLElement
     */
    public function asSimplexml();

    /**
     * Retrieve already merged layout updates from files for specified area/theme/package/store
     *
     * @return \Magento\Framework\View\Layout\Element
     */
    public function getFileLayoutUpdatesXml();

    /**
     * Retrieve containers from the update handles that have been already loaded
     *
     * Result format:
     * array(
     *     'container_name' => 'Container Label',
     *     // ...
     * )
     *
     * @return array
     */
    public function getContainers();
}
