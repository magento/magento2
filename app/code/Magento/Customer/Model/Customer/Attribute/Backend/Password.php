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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Model\Customer\Attribute\Backend;

use Magento\Framework\Model\Exception;

/**
 * Customer password attribute backend
 */
class Password extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Min password length
     */
    const MIN_PASSWORD_LENGTH = 6;

    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\String
     */
    protected $string;

    /**
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Stdlib\String $string
     */
    public function __construct(\Magento\Framework\Logger $logger, \Magento\Framework\Stdlib\String $string)
    {
        $this->string = $string;
        parent::__construct($logger);
    }

    /**
     * Special processing before attribute save:
     * a) check some rules for password
     * b) transform temporary attribute 'password' into real attribute 'password_hash'
     *
     * @param \Magento\Framework\Object $object
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    public function beforeSave($object)
    {
        $password = $object->getPassword();

        $length = $this->string->strlen($password);
        if ($length > 0) {
            if ($length < self::MIN_PASSWORD_LENGTH) {
                throw new Exception(__('The password must have at least %1 characters.', self::MIN_PASSWORD_LENGTH));
            }

            if ($this->string->substr(
                $password,
                0,
                1
            ) == ' ' || $this->string->substr(
                $password,
                $length - 1,
                1
            ) == ' '
            ) {
                throw new Exception(__('The password can not begin or end with a space.'));
            }

            $object->setPasswordHash($object->hashPassword($password));
        }
    }

    /**
     * @param \Magento\Framework\Object $object
     * @return bool
     */
    public function validate($object)
    {
        $password = $object->getPassword();
        if ($password && $password == $object->getPasswordConfirm()) {
            return true;
        }

        return parent::validate($object);
    }
}
