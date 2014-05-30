<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: TableEntityQuery.php 23167 2010-10-19 17:53:31Z mabe $
 */

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Storage
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_WindowsAzure_Storage_TableEntityQuery
{
    /**
     * From
     * 
     * @var string
     */
	protected $_from  = '';
	
	/**
	 * Where
	 * 
	 * @var array
	 */
	protected $_where = array();
	
	/**
	 * Order by
	 * 
	 * @var array
	 */
	protected $_orderBy = array();
	
	/**
	 * Top
	 * 
	 * @var int
	 */
	protected $_top = null;
	
	/**
	 * Partition key
	 * 
	 * @var string
	 */
	protected $_partitionKey = null;

	/**
	 * Row key
	 * 
	 * @var string
	 */
	protected $_rowKey = null;
	
	/**
	 * Select clause
	 * 
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function select()
	{
		return $this;
	}
	
	/**
	 * From clause
	 * 
	 * @param string $name Table name to select entities from
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function from($name)
	{
		$this->_from = $name;
		return $this;
	}
	
	/**
	 * Specify partition key
	 * 
	 * @param string $value Partition key to query for
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function wherePartitionKey($value = null)
	{
	    $this->_partitionKey = $value;
	    return $this;
	}
	
	/**
	 * Specify row key
	 * 
	 * @param string $value Row key to query for
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function whereRowKey($value = null)
	{
	    $this->_rowKey = $value;
	    return $this;
	}
	
	/**
	 * Add where clause
	 * 
	 * @param string       $condition   Condition, can contain question mark(s) (?) for parameter insertion.
	 * @param string|array $value       Value(s) to insert in question mark (?) parameters.
	 * @param string       $cond        Condition for the clause (and/or/not)
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function where($condition, $value = null, $cond = '')
	{
	    $condition = $this->_replaceOperators($condition);
	    
	    if ($value !== null) {
	        $condition = $this->_quoteInto($condition, $value);
	    }
	    
		if (count($this->_where) == 0) {
			$cond = '';
		} else if ($cond !== '') {
			$cond = ' ' . strtolower(trim($cond)) . ' ';
		}
		
		$this->_where[] = $cond . $condition;
		return $this;
	}

	/**
	 * Add where clause with AND condition
	 * 
	 * @param string       $condition   Condition, can contain question mark(s) (?) for parameter insertion.
	 * @param string|array $value       Value(s) to insert in question mark (?) parameters.
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function andWhere($condition, $value = null)
	{
		return $this->where($condition, $value, 'and');
	}
	
	/**
	 * Add where clause with OR condition
	 * 
	 * @param string       $condition   Condition, can contain question mark(s) (?) for parameter insertion.
	 * @param string|array $value       Value(s) to insert in question mark (?) parameters.
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function orWhere($condition, $value = null)
	{
		return $this->where($condition, $value, 'or');
	}
	
	/**
	 * OrderBy clause
	 * 
	 * @param string $column    Column to sort by
	 * @param string $direction Direction to sort (asc/desc)
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
	public function orderBy($column, $direction = 'asc')
	{
		$this->_orderBy[] = $column . ' ' . $direction;
		return $this;
	}
    
	/**
	 * Top clause
	 * 
	 * @param int $top  Top to fetch
	 * @return Zend_Service_WindowsAzure_Storage_TableEntityQuery
	 */
    public function top($top = null)
    {
        $this->_top  = (int)$top;
        return $this;
    }
	
    /**
     * Assembles the query string
     * 
     * @param boolean $urlEncode Apply URL encoding to the query string
     * @return string
     */
	public function assembleQueryString($urlEncode = false)
	{
		$query = array();
		if (count($this->_where) != 0) {
		    $filter = implode('', $this->_where);
			$query[] = '$filter=' . ($urlEncode ? self::encodeQuery($filter) : $filter);
		}
		
		if (count($this->_orderBy) != 0) {
		    $orderBy = implode(',', $this->_orderBy);
			$query[] = '$orderby=' . ($urlEncode ? self::encodeQuery($orderBy) : $orderBy);
		}
		
		if ($this->_top !== null) {
			$query[] = '$top=' . $this->_top;
		}
		
		if (count($query) != 0) {
			return '?' . implode('&', $query);
		}
		
		return '';
	}
	
	/**
	 * Assemble from
	 * 
	 * @param boolean $includeParentheses Include parentheses? ()
	 * @return string
	 */
	public function assembleFrom($includeParentheses = true)
	{
	    $identifier = '';
	    if ($includeParentheses) {
	        $identifier .= '(';
	        
	        if ($this->_partitionKey !== null) {
	            $identifier .= 'PartitionKey=\'' . $this->_partitionKey . '\'';
	        }
	            
	        if ($this->_partitionKey !== null && $this->_rowKey !== null) {
	            $identifier .= ', ';
	        }
	            
	        if ($this->_rowKey !== null) {
	            $identifier .= 'RowKey=\'' . $this->_rowKey . '\'';
	        }
	            
	        $identifier .= ')';
	    }
		return $this->_from . $identifier;
	}
	
	/**
	 * Assemble full query
	 * 
	 * @return string
	 */
	public function assembleQuery()
	{
		$assembledQuery = $this->assembleFrom();
		
		$queryString = $this->assembleQueryString();
		if ($queryString !== '') {
			$assembledQuery .= $queryString;
		}
		
		return $assembledQuery;
	}
	
	/**
	 * Quotes a variable into a condition
	 * 
	 * @param string       $text   Condition, can contain question mark(s) (?) for parameter insertion.
	 * @param string|array $value  Value(s) to insert in question mark (?) parameters.
	 * @return string
	 */
	protected function _quoteInto($text, $value = null)
	{
		if (!is_array($value)) {
	        $text = str_replace('?', '\'' . addslashes($value) . '\'', $text);
	    } else {
	        $i = 0;
	        while(strpos($text, '?') !== false) {
	            if (is_numeric($value[$i])) {
	                $text = substr_replace($text, $value[$i++], strpos($text, '?'), 1);
	            } else {
	                $text = substr_replace($text, '\'' . addslashes($value[$i++]) . '\'', strpos($text, '?'), 1);
	            }
	        }
	    }
	    return $text;
	}
	
	/**
	 * Replace operators
	 * 
	 * @param string $text
	 * @return string
	 */
	protected function _replaceOperators($text)
	{
	    $text = str_replace('==', 'eq',  $text);
	    $text = str_replace('>',  'gt',  $text);
	    $text = str_replace('<',  'lt',  $text);
	    $text = str_replace('>=', 'ge',  $text);
	    $text = str_replace('<=', 'le',  $text);
	    $text = str_replace('!=', 'ne',  $text);
	    
	    $text = str_replace('&&', 'and', $text);
	    $text = str_replace('||', 'or',  $text);
	    $text = str_replace('!',  'not', $text);
	    
	    return $text;
	}
	
	/**
	 * urlencode a query
	 * 
	 * @param string $query Query to encode
	 * @return string Encoded query
	 */
	public static function encodeQuery($query)
	{
		$query = str_replace('/', '%2F', $query);
		$query = str_replace('?', '%3F', $query);
		$query = str_replace(':', '%3A', $query);
		$query = str_replace('@', '%40', $query);
		$query = str_replace('&', '%26', $query);
		$query = str_replace('=', '%3D', $query);
		$query = str_replace('+', '%2B', $query);
		$query = str_replace(',', '%2C', $query);
		$query = str_replace('$', '%24', $query);
		
		
		$query = str_replace(' ', '%20', $query);
		
		return $query;
	}
	
	/**
	 * __toString overload
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return $this->assembleQuery();
	}
}