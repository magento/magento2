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
 * Layout argument. Type Array
 *
 * @category    Magento
 * @package     Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Model\Layout\Argument\Handler;

class ArrayHandler extends \Magento\Core\Model\Layout\Argument\AbstractHandler
{
    /**
     * @var \Magento\Core\Model\Layout\Argument\HandlerFactory
     */
    protected $_handlerFactory;

    /**
     * @param \Magento\Core\Model\Layout\Argument\HandlerFactory $handlerFactory
     */
    public function __construct(
        \Magento\Core\Model\Layout\Argument\HandlerFactory $handlerFactory
    ) {
        $this->_handlerFactory = $handlerFactory;
    }

    /**
     * Process Array argument
     *
     * @param array $argument
     * @throws \InvalidArgumentException
     * @return array
     */
    public function process(array $argument)
    {
        $this->_validate($argument);
        $result = array();
        foreach ($argument['value'] as $name => $item) {
            $result[$name] = $this->_handlerFactory
                ->getArgumentHandlerByType($item['type'])
                ->process($item);
        }
        return $result;
    }

    /**
     * @param array $argument
     * @throws \InvalidArgumentException
     */
    protected function _validate(array $argument)
    {
        parent::_validate($argument);
        $items = $argument['value'];
        if (!is_array($items)) {
            throw new \InvalidArgumentException(
                'Passed value has incorrect format. ' . $this->_getArgumentInfo($argument)
            );
        }
        foreach ($items as $name => $item) {
            if (!is_array($item) || !isset($item['type']) || !isset($item['value'])) {
                throw new \InvalidArgumentException(
                    'Array item: "' . $name . '" has incorrect format. ' . $this->_getArgumentInfo($argument)
                );
            }
        }
    }

    /**
     * Retrive value from Array argument
     *
     * @param \Magento\View\Layout\Element $argument
     * @return array|null
     */
    protected function _getArgumentValue(\Magento\View\Layout\Element $argument)
    {
        $items = $argument->xpath('item');
        if ($this->_isUpdater($argument) && empty($items)) {
            return null;
        }
        $result = array();
        /** @var $item \Magento\View\Layout\Element */
        foreach ($items as $item) {
            $itemName = (string)$item['name'];
            $itemType = $this->_getArgumentType($item);
            $result[$itemName] = $this->_handlerFactory
                ->getArgumentHandlerByType($itemType)
                ->parse($item);
        }
        return $result;
    }
}
