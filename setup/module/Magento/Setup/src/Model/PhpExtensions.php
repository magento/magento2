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

namespace Magento\Setup\Model;

use Magento\Composer\Reader\Json;

class PhpExtensions
{
    /**
     * File reader for composer.json files
     *
     * @var \Magento\Composer\Reader\Json
     */
    protected $fileReader;

    /**
     * List of required extensions
     *
     * @var array
     */
    protected $required = [];

    /**
     * List of currently installed extensions
     *
     * @var array
     */
    protected $current = [];

    /**
     * @param \Magento\Composer\Reader\Json $fileReader
     */
    public function __construct(
        Json $fileReader
    ) {
        $this->fileReader = $fileReader;
    }

    /**
     * Retrieve list of required extensions
     *
     * Collect required extensions from Magento modules composer.json files
     *
     * @return array
     */
    public function getRequired()
    {
        if (!$this->required) {
            $extensions = [];
            foreach ($this->fileReader->read() as $object) {
                if (!property_exists($object, 'require')) {
                    continue;
                }
                $items = get_object_vars($object->require);
                $items = array_filter(array_keys($items), [$this, 'filter']);
                if ($items) {
                    $extensions = array_merge($extensions, $items);
                }
            }

            $extensions = array_unique($extensions);
            array_walk($extensions, [$this, 'process']);

            $this->required = array_values($extensions);
            unset($extensions);
        }
        return $this->required;
    }

    /**
     * Retrieve list of currently installed extensions
     *
     * @return array
     */
    public function getCurrent()
    {
        if (!$this->current) {
            foreach ($this->required as $extension) {
                if (extension_loaded($extension)) {
                    $this->current[] = $extension;
                }
            }
        }
        return $this->current;
    }

    /**
     * Aplly filter to array of required items
     *
     * If item has prefix 'ext-' then return TRUE, otherwise return FALSE.
     *
     * @param string $value
     * @return bool
     */
    protected function filter($value)
    {
        return strpos($value, 'ext-') === 0;
    }

    /**
     * Process extension name
     *
     * Remove 'ext-' prefix from extension name.
     *
     * @param string $value
     */
    protected function process(&$value)
    {
        $value = preg_replace('/^ext-/', '', $value);
    }
}
