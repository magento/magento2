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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Session;

/**
 * Magento session save handler factory
 */
class SaveHandlerFactory
{
    /**
     * Php native session handler
     */
    const PHP_NATIVE_HANDLER = 'Magento\Framework\Session\SaveHandler\Native';

    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * Handlers
     *
     * @var array
     */
    protected $handlers = array();

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManager $objectManger
     * @param array $handlers
     */
    public function __construct(\Magento\Framework\ObjectManager $objectManger, array $handlers = array())
    {
        $this->objectManager = $objectManger;
        if (!empty($handlers)) {
            $this->handlers = array_merge($handlers, $this->handlers);
        }
    }

    /**
     * Create session save handler
     *
     * @param string $saveMethod
     * @param array $params
     * @return \SessionHandler
     * @throws \LogicException
     */
    public function create($saveMethod, $params = array())
    {
        $sessionHandler = self::PHP_NATIVE_HANDLER;
        if (isset($this->handlers[$saveMethod])) {
            $sessionHandler = $this->handlers[$saveMethod];
        }

        $model = $this->objectManager->create($sessionHandler, $params);
        if (!$model instanceof \SessionHandler) {
            throw new \LogicException($sessionHandler . ' doesn\'t implement \SessionHandler');
        }

        return $model;
    }
}
