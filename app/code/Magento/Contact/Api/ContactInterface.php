<?php

namespace Magento\Contact\Api;

use Magento\Contact\Api\Data\ContactFormInterface;

interface ContactInterface
{
    /**
     * Post data from contact form.
     *
     * @param ContactFormInterface $data
     * @return string
     */
    public function send(ContactFormInterface $data);
}
