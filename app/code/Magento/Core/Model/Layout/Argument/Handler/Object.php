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
 * Layout argument. Type object
 *
 * @category    Magento
 * @package     Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Model\Layout\Argument\Handler;

class Object extends \Magento\Core\Model\Layout\Argument\AbstractHandler
{
    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(\Magento\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Process argument value
     *
     * @param array $argument
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function process(array $argument)
    {
        $this->_validate($argument);
        $value = $argument['value'];

        return $this->_objectManager->create($value['object']);
    }

    /**
     * Validate argument
     * @param $argument
     * @throws \InvalidArgumentException
     */
    protected function _validate(array $argument)
    {
        parent::_validate($argument);
        $value = $argument['value'];

        if (!isset($value['object'])) {
            throw new \InvalidArgumentException(
                'Passed value has incorrect format. ' . $this->_getArgumentInfo($argument)
            );
        }

        if (!is_subclass_of($value['object'], 'Magento\Data\Collection')) {
            throw new \InvalidArgumentException(
                'Incorrect data source model. ' . $this->_getArgumentInfo($argument)
            );
        }
    }

    /**
     * Retrieve value from argument
     *
     * @param \Magento\View\Layout\Element $argument
     * @return array|null
     */
    protected function _getArgumentValue(\Magento\View\Layout\Element $argument)
    {
        $value = parent::_getArgumentValue($argument);
        if (!isset($value)) {
            return null;
        }
        return array(
            'object' => $value
        );
    }
}
