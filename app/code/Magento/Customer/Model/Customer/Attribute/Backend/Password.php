<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Customer\Attribute\Backend;

use Magento\Framework\Exception\LocalizedException;

/**
 * @deprecated 100.2.0
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
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @param \Magento\Framework\Stdlib\StringUtils $string
     */
    public function __construct(\Magento\Framework\Stdlib\StringUtils $string)
    {
        $this->string = $string;
    }

    /**
     * Special processing before attribute save:
     * a) check some rules for password
     * b) transform temporary attribute 'password' into real attribute 'password_hash'
     *
     * @param \Magento\Framework\DataObject $object
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave($object)
    {
        $password = $object->getPassword();

        $length = $this->string->strlen($password);
        if ($length > 0) {
            if ($length < self::MIN_PASSWORD_LENGTH) {
                throw new LocalizedException(
                    __(
                        'The password needs at least %1 characters. Create a new password and try again.',
                        self::MIN_PASSWORD_LENGTH
                    )
                );
            }

            if (trim($password) !== $password) {
                throw new LocalizedException(
                    __("The password can't begin or end with a space. Verify the password and try again.")
                );
            }

            $object->setPasswordHash($object->hashPassword($password));
        }
    }

    /**
     * @deprecated 100.2.0
     * @param \Magento\Framework\DataObject $object
     * @return bool
     */
    public function validate($object)
    {
        $password = $object->getPassword();
        if ($password && $password === $object->getPasswordConfirm()) {
            return true;
        }

        return parent::validate($object);
    }
}
