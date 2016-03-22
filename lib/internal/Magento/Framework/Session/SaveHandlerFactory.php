<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Handlers
     *
     * @var array
     */
    protected $handlers = [];

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManger
     * @param array $handlers
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManger, array $handlers = [])
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
    public function create($saveMethod, $params = [])
    {
        $sessionHandler = self::PHP_NATIVE_HANDLER;
        if (isset($this->handlers[$saveMethod])) {
            $sessionHandler = $this->handlers[$saveMethod];
        }

        $model = $this->objectManager->create($sessionHandler, $params);
        if (!$model instanceof \SessionHandlerInterface) {
            throw new \LogicException($sessionHandler . ' doesn\'t implement \SessionHandlerInterface');
        }

        return $model;
    }
}
