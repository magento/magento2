<?php
/**
 * Data source path visitor
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class  Magento_Datasource_Path_Visitor
{
    protected $_path = null;

    protected $_separator = '/';

    protected $_currentPathElement;

    public function __construct($path, $separator = '/')
    {
        $this->_path = trim($path, '{}');
        $this->_separator = $separator;
        return $this;
    }

    public function getCurrentPathElement()
    {
        return $this->_currentPathElement;
    }

    public function chopCurrentPathElement()
    {
        if (strpos($this->_path, $this->_separator) !== false) {
            $this->_currentPathElement = substr($this->_path, 0, strpos($this->_path, $this->_separator));
            $this->_path = substr($this->_path, strpos($this->_path, $this->_separator) + 1);
        } else {
            $this->_currentPathElement = $this->_path;
            $this->_path = '';
        }
        return $this->_currentPathElement;
    }

    /**
     * @param Magento_Datasource_Path_Visitable $object
     * @return null
     * @throws InvalidArgumentException
     */
    public function visitObject(Magento_Datasource_Path_Visitable $object)
    {
        $target = $object->visit($this);
        if ($target == null) {
            return null;
        }
        return $target;
    }

    public function visitArray(Array $target)
    {
        if (isset($target[$this->getCurrentPathElement()])) {
            return $target[$this->getCurrentPathElement()];
        } else {
            return null;
        }
    }

    public function visit($target)
    {
        $this->chopCurrentPathElement();
        if (is_array($target)) {
            $target = $this->visitArray($target);
        } else if (is_object($target)) {
            try {
                $target = $this->visitObject($target);
            } catch (Exception $e) {var_dump($e->getMessage());
                throw new \InvalidArgumentException('Search is not possible on the target object type');
            }
        } else {
            return null;
        }
        if (strlen($this->_path) == 0) {
            return $target;
        }
        return $this->visit($target);
    }
}