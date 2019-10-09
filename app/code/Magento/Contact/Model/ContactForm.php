<?php

namespace Magento\Contact\Model;

use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Contact\Api\Data\ContactFormInterface;

class ContactForm extends AbstractSimpleObject implements ContactFormInterface
{

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->_data[self::NAME];
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        if (trim($name) === '') {
            throw new \InvalidArgumentException(__('Enter the Name and try again.'));
        }

        $this->_data[self::NAME] = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getComment()
    {
        return $this->_data[self::COMMENT];
    }

    /**
     * {@inheritdoc}
     */
    public function setComment($comment)
    {
        if (trim($comment) === '') {
            throw new \InvalidArgumentException(__('Enter the comment and try again.'));
        }

        $this->_data[self::COMMENT] = $comment;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->_data[self::EMAIL];
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        if (false === \strpos($email, '@')) {
            throw new \InvalidArgumentException(__('The email address is invalid. Verify the email address and try again.'));
        }

        $this->_data[self::EMAIL] = $email;
    }

    /**
     * {@inheritdoc}
     */
    public function getTelephone()
    {
        return $this->_data[self::TELEPHONE];
    }

    /**
     * {@inheritdoc}
     */
    public function setTelephone($telephone)
    {
        $this->_data[self::TELEPHONE] = $telephone;
    }
}
