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
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $params = ['fieldName' => 'email'];
            throw new InputException(__('%fieldName is not a valid email address.', $params));
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
