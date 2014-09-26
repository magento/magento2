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
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\Data\Collection as DataCollection;

/**
 * Class ConfigurationStorageInterface
 */
interface ConfigStorageInterface
{
    /**
     * Add components configuration
     *
     * @param ConfigInterface $configuration
     * @return void
     */
    public function addComponentsData(ConfigInterface $configuration);

    /**
     * Remove components configuration
     *
     * @param ConfigInterface $configuration
     * @return void
     */
    public function removeComponentsData(ConfigInterface $configuration);

    /**
     * Get components configuration
     *
     * @param string|null $name
     * @return ConfigInterface|null|array
     */
    public function getComponentsData($name = null);

    /**
     * Add data in storage
     *
     * @param string $key
     * @param array $data
     * @return void
     */
    public function addData($key, array $data);

    /**
     * Remove data in storage
     *
     * @param string $key
     * @return void
     */
    public function removeData($key);

    /**
     * Get data from storage
     *
     * @param string|null $key
     * @return array|null
     */
    public function getData($key = null);

    /**
     * Update data in storage
     *
     * @param string $key
     * @param array $data
     * @return void
     */
    public function updateData($key, array $data);

    /**
     * Add meta data
     *
     * @param string $key
     * @param array $data
     * @return mixed
     */
    public function addMeta($key, array $data);

    /**
     * Remove meta data
     *
     * @param string $key
     * @return array
     */
    public function removeMeta($key);

    /**
     * Get meta data
     *
     * @param string|null $key
     * @return array|null
     */
    public function getMeta($key = null);

    /**
     * Update meta data in storage
     *
     * @param string $key
     * @param array $data
     * @return void
     */
    public function updateMeta($key, array $data);

    /**
     * Set data collection
     *
     * @param string $key
     * @param DataCollection $dataCollection
     * @return void
     */
    public function addDataCollection($key, DataCollection $dataCollection);

    /**
     * Get data collection
     *
     * @param string|null $key
     * @return DataCollection
     */
    public function getDataCollection($key = null);

    /**
     * Update data collection in storage
     *
     * @param string $key
     * @param DataCollection $dataCollection
     * @return mixed
     */
    public function updateDataCollection($key, DataCollection $dataCollection);

    /**
     * Add cloud data in storage
     *
     * @param string $key
     * @param array $data
     * @return void
     */
    public function addGlobalData($key, array $data);

    /**
     * Remove cloud data in storage
     *
     * @param string $key
     * @return void
     */
    public function removeGlobalData($key);

    /**
     * Get cloud data from storage
     *
     * @param string|null $key
     * @return array|null
     */
    public function getGlobalData($key = null);
}
