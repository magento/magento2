<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return ProcessorInterface
     */
    public function load($handles = []);

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

    /**
     * Return cache ID based current area/package/theme/store and handles
     *
     * @return string
     */
    public function getCacheId();
}
