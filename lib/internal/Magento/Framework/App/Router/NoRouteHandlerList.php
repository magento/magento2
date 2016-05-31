<?php
/**
 * No route handlers retriever
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Router;

class NoRouteHandlerList
{
    /**
     * No route handlers instances
     *
     * @var NoRouteHandlerInterface[]
     */
    protected $_handlers;

    /**
     * @var array
     */
    protected $_handlerList;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $handlerClassesList
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, array $handlerClassesList)
    {
        $this->_handlerList = $handlerClassesList;
        $this->_objectManager = $objectManager;
    }

    /**
     * Get noRoute handlers
     *
     * @return NoRouteHandlerInterface[]
     */
    public function getHandlers()
    {
        if (!$this->_handlers) {
            //sorting handlers list
            $sortedHandlersList = [];
            foreach ($this->_handlerList as $handlerInfo) {
                if (isset($handlerInfo['class']) && isset($handlerInfo['sortOrder'])) {
                    $sortedHandlersList[$handlerInfo['class']] = $handlerInfo['sortOrder'];
                }
            }

            asort($sortedHandlersList);

            //creating handlers
            foreach (array_keys($sortedHandlersList) as $handlerInstance) {
                $this->_handlers[] = $this->_objectManager->create($handlerInstance);
            }
        }

        return $this->_handlers;
    }
}
