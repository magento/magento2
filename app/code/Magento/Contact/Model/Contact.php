<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Model;
use Magento\Contact\Api\ContactInterface;
use Magento\Contact\Model\MailInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\InputException;

class Contact implements ContactInterface
{
	/**
	* @var MailInterface
	*/
    private $mail;

	/**
	* @param MailInterface $mail
	*/
    public function __construct(
        MailInterface $mail
    ) {
        $this->mail = $mail;
    }

	/**
	* {@inheritdoc}
	*/
    public function send($name, $email, $telephone = null, $comment)
    {
        $params = [];
        
        if (!is_string($name) || empty($name)) {
            $params[] = ['fieldName' => 'name'];
        }
        
        if (!is_string($email) || empty($email)) {
            $params[] = ['fieldName' => 'email'];
        }
    
        if (!is_string($comment) || empty($comment)) {
            $params[] = ['fieldName' => 'comment'];
        }
        
        if(!empty($params)){
            throw new InputException(__('%fieldName is a required field.', $params));
        }
        
        if (false === \strpos($email, '@')) {
            throw new InputException(__('Invalid email address'));
        }
        
        $contactData = ['name' => $name, 'email' => $email, 'telephone'=> $telephone, 'comment'=> $comment];
        $this->sendEmail($contactData);
        
        return true;
    }

    /**
     * @param array $contactData Contact data from contact form
     * @return void
     */
    private function sendEmail($contactData)
    {
        $this->mail->send(
            $contactData['email'],
            ['data' => new DataObject($contactData)]
        );
    }
}
