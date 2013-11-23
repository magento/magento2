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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Message;

/**
 * Message model factory
 */
class Factory
{
    /**
     * Error type
     */
    const ERROR = 'error';

    /**
     * Warning type
     */
    const WARNING = 'warning';

    /**
     * Notice type
     */
    const NOTICE = 'notice';

    /**
     * Success type
     */
    const SUCCESS = 'success';

    /**
     * Allowed message types
     *
     * @var array
     */
    protected $types = array(
        self::ERROR,
        self::WARNING,
        self::NOTICE,
        self::SUCCESS,
    );

    /**
     * Object Manager instance
     *
     * @var \Magento\ObjectManager
     */
    protected $objectManager;

    /**
     * Factory constructor
     *
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(\Magento\ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create message instance with specified parameters
     *
     * @param $type
     * @param string $code
     * @param string $class
     * @param string $method
     * @throws \InvalidArgumentException
     * @return AbstractMessage
     */
    public function create($type, $code = '', $class = '', $method = '')
    {
        if (!in_array($type, $this->types)) {
            throw new \InvalidArgumentException('Wrong message type');
        }

        $className = 'Magento\Message\\' . ucfirst($type);
        $message = $this->objectManager->create($className, array('code' => $code));
        if (!($message instanceof AbstractMessage)) {
            throw new \InvalidArgumentException($className . ' doesn\'t extends \Magento\Message\AbstractMessage');
        }

        $message->setClass($class);
        $message->setMethod($method);

        return $message;
    }

    /**
     * Create error message
     *
     * @param $code
     * @param string $class
     * @param string $method
     * @return Error
     */
    public function error($code, $class='', $method='')
    {
        return $this->create(self::ERROR, $code, $class, $method);
    }

    /**
     * Create warning message
     *
     * @param $code
     * @param string $class
     * @param string $method
     * @return Warning
     */
    public function warning($code, $class='', $method='')
    {
        return $this->create(self::WARNING, $code, $class, $method);
    }

    /**
     * Create notice message
     *
     * @param $code
     * @param string $class
     * @param string $method
     * @return Notice
     */
    public function notice($code, $class='', $method='')
    {
        return $this->create(self::NOTICE, $code, $class, $method);
    }

    /**
     * Create success message
     *
     * @param $code
     * @param string $class
     * @param string $method
     * @return Success
     */
    public function success($code, $class='', $method='')
    {
        return $this->create(self::SUCCESS, $code, $class, $method);
    }
}
