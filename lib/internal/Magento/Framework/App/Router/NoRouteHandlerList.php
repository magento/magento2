<?php
/**
 * No route handlers retriever
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Router;

/**
 * Class \Magento\Framework\App\Router\NoRouteHandlerList
 *
 * @since 2.0.0
 */
class NoRouteHandlerList
{
    /**
     * No route handlers instances
     *
     * @var NoRouteHandlerInterface[]
     * @since 2.0.0
     */
    protected $_handlers;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $_handlerList;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $handlerClassesList
     * @since 2.0.0
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
     * @since 2.0.0
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
