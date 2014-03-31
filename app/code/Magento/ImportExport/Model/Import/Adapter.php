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
 * @package     Magento_ImportExport
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ImportExport\Model\Import;

use Magento\Filesystem\Directory\Write;
use Magento\ImportExport\Model\Import\AbstractSource;

/**
 * Import adapter model
 *
 * @category    Magento
 * @package     Magento_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Adapter
{
    /**
     * Adapter factory. Checks for availability, loads and create instance of import adapter object.
     *
     * @param string $type Adapter type ('csv', 'xml' etc.)
     * @param Write $directory
     * @param mixed $options OPTIONAL Adapter constructor options
     * @return AbstractSource
     * @throws \Magento\Model\Exception
     */
    public static function factory($type, $directory, $options = null)
    {
        if (!is_string($type) || !$type) {
            throw new \Magento\Model\Exception(__('The adapter type must be a non empty string.'));
        }
        $adapterClass = 'Magento\ImportExport\Model\Import\Source\\' . ucfirst(strtolower($type));

        if (!class_exists($adapterClass)) {
            throw new \Magento\Model\Exception("'{$type}' file extension is not supported");
        }
        $adapter = new $adapterClass($options, $directory);

        if (!$adapter instanceof AbstractSource) {
            throw new \Magento\Model\Exception(
                __('Adapter must be an instance of \Magento\ImportExport\Model\Import\AbstractSource')
            );
        }
        return $adapter;
    }

    /**
     * Create adapter instance for specified source file.
     *
     * @param string $source Source file path.
     * @param Write $directory
     * @return AbstractSource
     */
    public static function findAdapterFor($source, $directory)
    {
        return self::factory(pathinfo($source, PATHINFO_EXTENSION), $directory, $source);
    }
}
