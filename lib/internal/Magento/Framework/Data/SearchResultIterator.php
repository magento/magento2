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
namespace Magento\Framework\Data;

use Magento\Framework\DB\QueryInterface;
use Magento\Framework\Data\AbstractSearchResult;

/**
 * Class SearchResultIterator
 */
class SearchResultIterator implements \Iterator
{
    /**
     * @var SearchResultInterface
     */
    protected $searchResult;

    /**
     * @var QueryInterface
     */
    protected $query;

    /**
     * @var array
     */
    protected $current;

    /**
     * @var int
     */
    protected $key = 0;

    /**
     * @param AbstractSearchResult $searchResult
     * @param QueryInterface $query
     */
    public function __construct(AbstractSearchResult $searchResult, QueryInterface $query)
    {
        $this->searchResult = $searchResult;
        $this->query = $query;
    }

    /**
     * @return array|mixed
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * @return void
     */
    public function next()
    {
        ++$this->key;
        $this->current = $this->searchResult->createDataObject($this->query->fetchItem());
    }

    /**
     * @return int|mixed
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return !empty($this->current);
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->current = null;
        $this->key = 0;
        $this->query->reset();
        $this->next();
    }
}
