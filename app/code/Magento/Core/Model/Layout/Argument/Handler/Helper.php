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
 * Layout argument. Type helper.
 *
 * @category    Magento
 * @package     Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Model\Layout\Argument\Handler;

class Helper extends \Magento\Core\Model\Layout\Argument\AbstractHandler
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

        $helper = $this->_objectManager->get($value['helperClass']);
        return call_user_func_array(array($helper, $value['helperMethod']), $value['params']);
    }

    /**
     * @param array $argument
     * @throws \InvalidArgumentException
     */
    protected function _validate(array $argument)
    {
        parent::_validate($argument);
        $value = $argument['value'];

        if (!isset($value['helperClass']) || !isset($value['helperMethod'])) {
            throw new \InvalidArgumentException(
                'Passed helper has incorrect format. ' . $this->_getArgumentInfo($argument)
            );
        }
        if (!method_exists($value['helperClass'], $value['helperMethod'])) {
            throw new \InvalidArgumentException(
                'Helper method "' . $value['helperClass'] . '::' . $value['helperMethod'] . '" does not exist.'
                . ' ' . $this->_getArgumentInfo($argument)
            );
        }
    }

    /**
     * Retrieve value from argument
     *
     * @param \Magento\View\Layout\Element $argument
     * @return array
     */
    protected function _getArgumentValue(\Magento\View\Layout\Element $argument)
    {
        $value = array(
            'helperClass' => '',
            'helperMethod' => '',
            'params' => array(),
        );

        list($value['helperClass'], $value['helperMethod']) = explode('::', $argument['helper']);

        if (isset($argument->param)) {
            foreach ($argument->param as $param) {
                $value['params'][] = (string)$param;
            }
        }
        return $value;
    }
}
