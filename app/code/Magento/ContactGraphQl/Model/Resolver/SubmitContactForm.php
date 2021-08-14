<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ContactGraphQl\Model\Resolver;

use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Model\MailInterface;
use Magento\Contact\Model\MailParamsValidator;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class SubmitContactForm implements ResolverInterface
{
    /**
     * @var \Magento\Contact\Model\ConfigInterface
     */
    private $contactConfig;

    /**
     * @var \Magento\Contact\Model\MailInterface
     */
    private $mail;

    /**
     * @var \Magento\Contact\Model\MailParamsValidator
     */
    private $paramsValidator;
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param \Magento\Contact\Model\ConfigInterface $contactConfig
     * @param \Magento\Contact\Model\MailInterface $mail
     * @param \Magento\Contact\Model\MailParamsValidator $paramsValidator
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        ConfigInterface $contactConfig,
        MailInterface $mail,
        MailParamsValidator $paramsValidator,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->contactConfig = $contactConfig;
        $this->mail = $mail;
        $this->paramsValidator = $paramsValidator;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Handle a Contact form submission
     *
     * @param \Magento\Framework\GraphQl\Config\Element\Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param \Magento\Framework\GraphQl\Schema\Type\ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return bool[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {
        if (!$this->contactConfig->isEnabled()) {
            throw new LocalizedException(__('Feature disabled.'));
        }

        $input = $args['input'] ?? null;

        if (!$input || !\is_array($input)) {
            throw new GraphQlInputException(__('Incorrect parameters.'));
        }

        $params = $this->dataObjectFactory->create(['data' => $input]);
        $this->paramsValidator->validate($params);
        $this->mail->send(
            $params->getData('email'),
            ['data' => $params]
        );

        return ['success' => true];
    }
}
