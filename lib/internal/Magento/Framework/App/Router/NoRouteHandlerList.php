<?php
/**
 * No route handlers retriever
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
