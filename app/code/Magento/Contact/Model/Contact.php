<?php

namespace Magento\Contact\Model;

use Magento\Contact\Api\ContactInterface;
use Magento\Contact\Api\Data\ContactFormInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class Contact
 * @package Phoenix\Contact\Model
 */
class Contact implements ContactInterface
{
    /**
     * @var MailInterface
     */
    private $mail;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Contact constructor.
     * @param MailInterface $mail
     * @param SerializerInterface $serializer
     */
    public function __construct(
        MailInterface $mail,
        SerializerInterface $serializer
    ) {
        $this->mail = $mail;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function send(ContactFormInterface $data)
    {
        $result = [];
        try {
            $this->mail->send($data->getEmail(), ['data' => new DataObject($data->__toArray())]);
            $result['message'] = __('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.');
        } catch (LocalizedException $e) {
            $result['error'] = $e->getMessage();
        } catch (\Exception $e) {
            $result['error'] = __('An error occurred while processing your form. Please try again later.');
        }

        return $this->serializer->serialize($result);
    }
}
