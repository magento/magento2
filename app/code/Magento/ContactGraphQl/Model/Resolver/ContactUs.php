<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ContactGraphQl\Model\Resolver;

use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Model\MailInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Psr\Log\LoggerInterface;
use Magento\ContactGraphQl\Model\ContactUsValidator;

class ContactUs implements ResolverInterface
{
    /**
     * @var MailInterface
     */
    private MailInterface $mail;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $contactConfig;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var ContactUsValidator
     */
    private ContactUsValidator $validator;

    /**
     * @param MailInterface $mail
     * @param ConfigInterface $contactConfig
     * @param LoggerInterface $logger
     * @param ContactUsValidator $validator
     */
    public function __construct(
        MailInterface $mail,
        ConfigInterface $contactConfig,
        LoggerInterface $logger,
        ContactUsValidator $validator
    ) {
        $this->mail = $mail;
        $this->contactConfig = $contactConfig;
        $this->logger = $logger;
        $this->validator = $validator;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (!$this->contactConfig->isEnabled()) {
            throw new GraphQlInputException(
                __('The contact form is unavailable.')
            );
        }

        $input = array_map(function ($field) {
            return $field === null ? '' : trim($field);
        }, $args['input']);
        $this->validator->execute($input);

        try {
            $this->mail->send($input['email'], ['data' => $input]);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new GraphQlInputException(
                __('An error occurred while processing your form. Please try again later.')
            );
        }

        return [
            'status' => true
        ];
    }
}
