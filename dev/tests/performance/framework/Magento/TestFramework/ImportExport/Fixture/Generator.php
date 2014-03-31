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

/**
 * A custom "Import" adapter for Magento_ImportExport module that allows generating arbitrary data rows
 */
namespace Magento\TestFramework\ImportExport\Fixture;

class Generator extends \Magento\ImportExport\Model\Import\AbstractSource
{
    /**
     * Data row pattern
     *
     * @var array
     */
    protected $_pattern = array();

    /**
     * Which columns are determined as dynamic
     *
     * @var array
     */
    protected $_dynamicColumns = array();

    /**
     * @var int
     */
    protected $_limit = 0;

    /**
     * Read the row pattern to determine which columns are dynamic, set the collection size
     *
     * @param array $rowPattern
     * @param int $limit how many records to generate
     */
    public function __construct(array $rowPattern, $limit)
    {
        foreach ($rowPattern as $key => $value) {
            if (is_callable($value) || is_string($value) && false !== strpos($value, '%s')) {
                $this->_dynamicColumns[$key] = $value;
            }
        }
        $this->_pattern = $rowPattern;
        $this->_limit = (int)$limit;
        parent::__construct(array_keys($rowPattern));
    }

    /**
     * Whether limit of generated elements is reached (according to "Iterator" interface)
     *
     * @return bool
     */
    public function valid()
    {
        return $this->_key + 1 <= $this->_limit;
    }

    protected function _getNextRow()
    {
        $row = $this->_pattern;
        foreach ($this->_dynamicColumns as $key => $dynamicValue) {
            $index = $this->_key + 1;
            if (is_callable($dynamicValue)) {
                $row[$key] = call_user_func($dynamicValue, $index);
            } else {
                $row[$key] = str_replace('%s', $index, $dynamicValue);
            }
        }
        return $row;
    }
}
