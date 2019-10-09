<?php

namespace Magento\Contact\Api\Data;

interface ContactFormInterface
{
    const NAME = 'name';
    const COMMENT = 'comment';
    const EMAIL = 'email';
    const TELEPHONE = 'telephone';

    /**
     * @return string
     */
    public function getName();
    /**
     * @param string $name
     */
    public function setName($name);
    /**
     * @return string
     */
    public function getComment();
    /**
     * @param string $comment
     */
    public function setComment($comment);
    /**
     * @return string
     */
    public function getEmail();
    /**
     * @param string $email
     */
    public function setEmail($email);
    /**
     * @return string
     */
    public function getTelephone();
    /**
     * @param string $telephone
     */
    public function setTelephone($telephone);
}
