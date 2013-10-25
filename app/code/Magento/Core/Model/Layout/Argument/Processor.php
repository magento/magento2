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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Layout argument processor
 *
 * @category    Magento
 * @package     Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Model\Layout\Argument;

class Processor
{
    /**
     * @var \Magento\Core\Model\Layout\Argument\HandlerFactory
     */
    protected $_handlerFactory;

    /**
     * @var \Magento\Core\Model\Layout\Argument\Updater
     */
    protected $_argumentUpdater;

    /**
     * Argument handlers object list
     *
     * @var array
     */
    protected $_argumentHandlers = array();

    /**
     * @param \Magento\Core\Model\Layout\Argument\Updater $argumentUpdater
     * @param \Magento\Core\Model\Layout\Argument\HandlerFactory $handlerFactory
     */
    public function __construct(
        \Magento\Core\Model\Layout\Argument\Updater $argumentUpdater,
        \Magento\Core\Model\Layout\Argument\HandlerFactory $handlerFactory
    ) {
        $this->_handlerFactory  = $handlerFactory;
        $this->_argumentUpdater = $argumentUpdater;
    }

    /**
     * Parse given argument
     *
     * @param \Magento\View\Layout\Element $argument
     * @throws \InvalidArgumentException
     * @return array
     */
    public function parse(\Magento\View\Layout\Element $argument)
    {
        $type = $this->_getArgumentType($argument);
        $handler = $this->_handlerFactory->getArgumentHandlerByType($type);
        return $handler->parse($argument);
    }

    /**
     * Process given argument
     *
     * @param array $argument
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function process(array $argument)
    {
        $handler = $this->_handlerFactory->getArgumentHandlerByType($argument['type']);
        $result = $handler->process($argument);
        if (!empty($argument['updaters'])) {
            $result = $this->_argumentUpdater->applyUpdaters($result, $argument['updaters']);
        }
        return $result;
    }

    /**
     * Get Argument's XSI type
     *
     * @param \Magento\View\Layout\Element $argument
     * @return string
     */
    protected function _getArgumentType(\Magento\View\Layout\Element $argument)
    {
        return (string)$argument->attributes('xsi', true)->type;
    }
}
