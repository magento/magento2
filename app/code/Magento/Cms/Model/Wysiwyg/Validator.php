<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Wysiwyg;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Validator\HTML\WYSIWYGValidatorInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Message\Factory as MessageFactory;

/**
 * Processes backend validator results.
 */
class Validator implements WYSIWYGValidatorInterface
{
    public const CONFIG_PATH_THROW_EXCEPTION = 'cms/wysiwyg/force_valid';

    /**
     * @var WYSIWYGValidatorInterface
     */
    private $validator;

    /**
     * @var ManagerInterface
     */
    private $messages;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @param WYSIWYGValidatorInterface $validator
     * @param ManagerInterface $messages
     * @param ScopeConfigInterface $config
     * @param LoggerInterface $logger
     * @param MessageFactory $messageFactory
     */
    public function __construct(
        WYSIWYGValidatorInterface $validator,
        ManagerInterface $messages,
        ScopeConfigInterface $config,
        LoggerInterface $logger,
        MessageFactory $messageFactory
    ) {
        $this->validator = $validator;
        $this->messages = $messages;
        $this->config = $config;
        $this->logger = $logger;
        $this->messageFactory = $messageFactory;
    }

    /**
     * @inheritDoc
     */
    public function validate(string $content): void
    {
        $throwException = $this->config->isSetFlag(self::CONFIG_PATH_THROW_EXCEPTION);
        try {
            $this->validator->validate($content);
        } catch (ValidationException $exception) {
            if ($throwException) {
                throw $exception;
            } else {
                $this->messages->addUniqueMessages(
                    [
                        $this->messageFactory->create(
                            MessageInterface::TYPE_WARNING,
                            (string)__(
                                'Temporarily allowed to save HTML value that contains restricted elements. %1',
                                $exception->getMessage()
                            )
                        )
                    ]
                );
            }
        } catch (\Throwable $exception) {
            if ($throwException) {
                throw $exception;
            } else {
                $this->messages->addUniqueMessages(
                    [
                        $this->messageFactory->create(
                            MessageInterface::TYPE_WARNING,
                            (string)__('Invalid HTML provided')
                        )
                    ]
                );
                $this->logger->error($exception);
            }
        }
    }
}
