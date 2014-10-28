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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Simplexml\Config\Cache;

/**
 * Abstract class for configuration cache
 * @method void setComponents(array $components)
 * @method void setIsAllowedToSave(bool $isAllowedToSave)
 * @method array getComponents()
 */
abstract class AbstractCache extends \Magento\Framework\Object
{
    /**
     * Constructor
     *
     * Initializes components and allows to save the cache
     *
     * @param array $data
     */
    public function __construct($data = array())
    {
        parent::__construct($data);

        $this->setComponents(array());
        $this->setIsAllowedToSave(true);
    }

    /**
     * Add configuration component to stats
     *
     * @param string $component Filename of the configuration component file
     * @return $this
     */
    public function addComponent($component)
    {
        $comps = $this->getComponents();
        if (is_readable($component)) {
            $comps[$component] = array('mtime' => filemtime($component));
        }
        $this->setComponents($comps);

        return $this;
    }

    /**
     * Validate components in the stats
     *
     * @param array $data
     * @return boolean
     */
    public function validateComponents($data)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }
        // check that no source files were changed or check file exists
        foreach ($data as $sourceFile => $stat) {
            if (empty($stat['mtime']) || !is_file($sourceFile) || filemtime($sourceFile) !== $stat['mtime']) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return string
     */
    public function getComponentsHash()
    {
        $sum = '';
        foreach ($this->getComponents() as $comp) {
            $sum .= $comp['mtime'] . ':';
        }
        $hash = md5($sum);
        return $hash;
    }

    /**
     * @return bool
     */
    abstract public function load();

    /**
     * @return bool
     */
    abstract public function save();
}
