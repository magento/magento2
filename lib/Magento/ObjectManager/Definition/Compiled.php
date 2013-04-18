<?php
/**
 * Compiled class definitions. Should be used for maximum performance in production.
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     {license_link
 */
abstract class Magento_ObjectManager_Definition_Compiled implements Magento_ObjectManager_Definition
{
    /**
     * Class definitions
     *
     * @var array
     */
    protected $_definitions;

    /**
     * @param array $definitions
     */
    public function __construct(array $definitions)
    {
        list($this->_signatures, $this->_definitions) = $definitions;
    }

    /**
     * Unpack signature
     *
     * @param string $signature
     * @return mixed
     */
    abstract protected function _unpack($signature);

    /**
     * Get list of method parameters
     *
     * Retrieve an ordered list of constructor parameters.
     * Each value is an array with following entries:
     *
     * array(
     *     0, // string: Parameter name
     *     1, // string|null: Parameter type
     *     2, // bool: whether this param is required
     *     3, // mixed: default value
     * );
     *
     * @param string $className
     * @return array|null
     */
    public function getParameters($className)
    {
        $definition = $this->_definitions[$className];
        if ($definition !== null) {
            if (is_string($this->_signatures[$definition])) {
                $this->_signatures[$definition] = $this->_unpack($this->_signatures[$definition]);
            }
            return $this->_signatures[$definition];
        }
        return null;
    }
}
