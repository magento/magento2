<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Menu\Builder;

/**
 * Menu builder command
 */
abstract class AbstractCommand
{
    /**
     * List of required params
     *
     * @var string[]
     */
    protected $_requiredParams = ["id"];

    /**
     * Command params array
     *
     * @var array
     */
    protected $_data = [];

    /**
     * Next command in the chain
     *
     * @var \Magento\Backend\Model\Menu\Builder\AbstractCommand
     */
    protected $_next = null;

    /**
     * @param array $data
     * @throws \InvalidArgumentException
     */
    public function __construct(array $data = [])
    {
        foreach ($this->_requiredParams as $param) {
            if (!isset($data[$param]) || $data[$param] === null) {
                throw new \InvalidArgumentException("Missing required param " . $param);
            }
        }
        $this->_data = $data;
    }

    /**
     * Retrieve id of element to apply command to
     *
     * @return int
     */
    public function getId()
    {
        return $this->_data['id'];
    }

    /**
     * Add command as last in the list of callbacks
     *
     * @param \Magento\Backend\Model\Menu\Builder\AbstractCommand $command
     * @return $this
     * @throws \InvalidArgumentException if invalid chaining command is supplied
     */
    public function chain(\Magento\Backend\Model\Menu\Builder\AbstractCommand $command)
    {
        if ($this->_next === null) {
            $this->_next = $command;
        } else {
            $this->_next->chain($command);
        }
        return $this;
    }

    /**
     * Execute command and pass control to chained commands
     *
     * @param array $itemParams
     * @return array
     */
    public function execute(array $itemParams = [])
    {
        $itemParams = $this->_execute($itemParams);
        if ($this->_next !== null) {
            $itemParams = $this->_next->execute($itemParams);
        }
        return $itemParams;
    }

    /**
     * Execute internal command actions
     *
     * @param array $itemParams
     * @return array
     */
    abstract protected function _execute(array $itemParams);
}
