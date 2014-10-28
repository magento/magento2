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
namespace Magento\TestFramework\ImportExport\Fixture\Complex;

/**
 * Class Generator
 *
 *
 */
class Generator extends \Magento\ImportExport\Model\Import\AbstractSource
{
    /**
     * Data row pattern
     *
     * @var \Magento\TestFramework\ImportExport\Fixture\Complex\Pattern
     */
    protected $_pattern;

    /**
     * Entities limit
     *
     * @var int
     */
    protected $_limit = 0;

    /**
     * Entities Count
     *
     * @var int
     */
    protected $_count = 0;

    /**
     * Array of template variables (static values or callables)
     *
     * @var array
     */
    protected $_variables = array();

    /**
     * Current index
     *
     * @var int
     */
    protected $_index = 1;

    /**
     * Rows count in pattern
     *
     * @var int
     */
    protected $_patternRowsCount = 0;

    /**
     * Read the row pattern to determine which columns are dynamic, set the collection size
     *
     * @param Pattern $rowPattern
     * @param int $count how many records to generate
     */
    public function __construct(Pattern $rowPattern, $count)
    {
        $this->_pattern = $rowPattern;
        $this->_count = $count;
        $this->_patternRowsCount = $this->_pattern->getRowsCount();
        $this->_limit = (int)$count * $this->_patternRowsCount;
        parent::__construct($this->_pattern->getHeaders());
    }

    /**
     * Get row index for template
     *
     * @param $key
     *
     * @return float
     */
    public function getIndex($key)
    {
        return floor($key / $this->_patternRowsCount) + 1;
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

    /**
     * Get next row in set
     *
     * @return array|bool
     */
    protected function _getNextRow()
    {
        $key = $this->key();
        $this->_index = $this->getIndex($key);

        if ($key > $this->_limit) {
            return false;
        }
        return $this->_pattern->getRow($this->_index, $key);
    }

    /**
     * Return the current element
     *
     * Returns the row in associative array format: array(<col_name> => <value>, ...)
     *
     * @return array
     */
    public function current()
    {
        return $this->_row;
    }
}
