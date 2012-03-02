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
 * @category    Mage
 * @package     Mage_Connect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Connect_Structures_Node
{
    
    protected $_data = null;    
    protected $_metadata = array();    
    protected $_arcs = array();    
    protected $_graph = null;
    
    /**
     * Node graph getter
     *
     * @return Mage_Connect_Structures_Graph
     */
    public function &getGraph() 
    {
        return $this->_graph;
    }

    /**
     *
     * Node graph setter. 
     * This method should not be called directly. 
     * Use Graph::addNode instead.
     *
     * @param $graph
     */
    public function setGraph(&$graph) 
    {
        $this->_graph =& $graph;
    }

    /**
     *
     * Node data getter.
     *
     * Each graph node can contain a reference to one variable. This is the getter for that reference.
     *
     * @return   mixed   Data stored in node
     * @access   public
     */
    public function &getData() 
    {
        return $this->_data;
    }

    /**
     * Node data setter
     * 
     * Each graph node can contain a reference to one variable. This is the setter for that reference.
     *   
     * @return   mixed   Data to store in node
     */
    public function setData($data) 
    {
        $this->_data =& $data;
    }

    /**
     *
     * Test for existence of metadata under a given key.
     *
     * @param    string    Key to test
     * @return   boolean
     * @access   public
     */
    public function metadataKeyExists($key) 
    {
        return array_key_exists($key, $this->_metadata);
    }

    /**
     *
     * Get node metadata
     *
     * @param    string  $key 
     * @param    boolean $nullIfNonexistent (defaults to false).
     * @return   mixed   
     */
    public function & getMetadata($key, $nullIfNonexistent = false) 
    {
        if (array_key_exists($key, $this->_metadata)) {
            return $this->_metadata[$key];
        } elseif ($nullIfNonexistent) {
                $a = null;
                return $a;
        } else {
            throw new Exception(__METHOD__." : requested key doesn't exist: {$key}");            
        }
    }

    /**
     *
     * Delete metadata by key
     *
     * @param    string  Key
     */
    public function unsetMetadata($key) 
    {
        if (array_key_exists($key, $this->_metadata)) {
            unset($this->_metadata[$key]);
        }    
        
    }
        
    /**
     *
     * Node metadata setter
     *
     * Each graph node can contain multiple 'metadata' entries, each stored under a different key, as in an
     * associative array or in a dictionary. This method stores data under the given key. If the key already exists,
     * previously stored data is discarded.
     *
     * @param    string  $key
     * @param    mixed   $data
     */
    public function setMetadata($key, $data) 
    {
        $this->_metadata[$key] =& $data;
    }

    protected function _connectTo(&$destinationNode) 
    {
        $this->_arcs[] =& $destinationNode;
    }

    /**
     * Connect this node to another one.
     * If the graph is not directed, the reverse arc, connecting $destinationNode to $this is also created.
     * @param    Structures_Graph Node to connect to
     */
    public function connectTo(&$destinationNode)
    {
        $class = get_class($this);
        if(!$destinationNode instanceof $class) {
            throw new Exception(__METHOD__." : argument should be instance of {$class}");
        }
         
        // Nodes must already be in graphs to be connected
        if ($this->_graph == null) {
            throw new Exception(__METHOD__." : tried to connect to null graph");
        }

        if ($destinationNode->getGraph() == null) {
            throw new Exception(__METHOD__." : tried to connect to node that is not connected to any graph");
        }

        // Connect here
        $this->_connectTo($destinationNode);
        // If graph is undirected, connect back
        if (!$this->_graph->isDirected()) {
            $destinationNode->_connectTo($this);
        }
    }


    /**
     * Return nodes connected to this one.
     * @return array 
     */
    public function getNeighbours()
    {
        return $this->_arcs;
    }

    /**
     * Test wether this node has an arc to the target node
     * Returns true if the two nodes are connected
     * @return boolean   
     */
    public function connectsTo(&$target)
    {
        $arcKeys = array_keys($this->_arcs);
        foreach($arcKeys as $key) {
            $arc =& $this->_arcs[$key];
            if ($target === $arc) { 
                return true;
            }
        }
        return false;
    }

    /**
     * Calculate the in degree of the node.
     *
     * The indegree for a node is the number of arcs 
     * entering the node. 
     * 
     * For non directed graphs:
     *  always outdegree = indegree.
     *  
     * @return int
     */
    public function inDegree() 
    {
        $result = 0;

        if ($this->_graph == null) {
            return $result;
        }
        if (!$this->_graph->isDirected()) {
            return $this->outDegree();
        }

        $graphNodes =& $this->_graph->getNodes();
        foreach (array_keys($graphNodes) as $key) {
            if ($graphNodes[$key]->connectsTo($this)) {
                $result++;
            }
        }
        return $result;

    }

    /**
     * Calculate the out degree of the node.
     *
     * The outdegree for a node is the number of arcs exiting the node. 
     * For non directed graphs:
     *  always outdegree = indegree.
     *
     * @return int
     */
    public function outDegree()
    {
        if ($this->_graph == null) {
            return 0;
        }
        return count($this->_arcs);
    }

}
