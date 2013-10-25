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
 * Layout argument. Type url
 *
 * @category    Magento
 * @package     Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Model\Layout\Argument\Handler;

class Url extends \Magento\Core\Model\Layout\Argument\AbstractHandler
{
    /**
     * @var \Magento\UrlInterface
     */
    protected $_urlModel;

    /**
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\UrlInterface $urlModel
     */
    public function __construct(\Magento\UrlInterface  $urlModel)
    {
        $this->_urlModel = $urlModel;
    }

    /**
     * Generate url
     *
     * @param array $argument
     * @return string
     * @throws \InvalidArgumentException
     */
    public function process(array $argument)
    {
        $this->_validate($argument);
        $value = $argument['value'];

        return $this->_urlModel->getUrl($value['path'], $value['params']);
    }

    /**
     * @param array $argument
     * @throws \InvalidArgumentException
     */
    protected function _validate(array $argument)
    {
        parent::_validate($argument);
        $value = $argument['value'];

        if (!isset($value['path'])) {
            throw new \InvalidArgumentException(
                'Passed value has incorrect format. ' . $this->_getArgumentInfo($argument)
            );
        }
    }

    /**
     * @param $argument
     * @return array
     */
    protected function _getArgumentValue(\Magento\View\Layout\Element $argument)
    {
        $result = array(
            'path' => (string)$argument['path'],
            'params' => array()
        );

        if (isset($argument->param)) {
            foreach ($argument->param as $param) {
                $result['params'][(string)$param['name']] = (string)$param;
            }
        }

        return $result;
    }
}
