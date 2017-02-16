<?php
/**
 * Contact module base controller
 */
namespace Magento\Contact\Api;

interface MailInterface
{
    /**
     * @param string $replyTo Reply-to email address
     * @param array $variables Email template variables
     * @return void
     */
    public function send($replyTo, $variables);
}