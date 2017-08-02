<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

/**
 * Class ValidatorResultMerger
 * @since 2.2.0
 */
class ValidatorResultMerger
{
    /**
     * @var ValidatorResultInterfaceFactory
     * @since 2.2.0
     */
    private $validatorResultInterfaceFactory;

    /**
     * ValidatorResultMerger constructor.
     *
     * @param ValidatorResultInterfaceFactory $validatorResultInterfaceFactory
     * @since 2.2.0
     */
    public function __construct(ValidatorResultInterfaceFactory $validatorResultInterfaceFactory)
    {
        $this->validatorResultInterfaceFactory = $validatorResultInterfaceFactory;
    }

    /**
     * Merge two validator results and additional messages
     *
     * @param ValidatorResultInterface $first
     * @param ValidatorResultInterface $second
     * @param \string[] $validatorMessages
     * @return ValidatorResultInterface
     * @since 2.2.0
     */
    public function merge(ValidatorResultInterface $first, ValidatorResultInterface $second, ... $validatorMessages)
    {
        $messages = array_merge($first->getMessages(), $second->getMessages(), ...$validatorMessages);

        $result = $this->validatorResultInterfaceFactory->create();
        foreach ($messages as $message) {
            $result->addMessage($message);
        }

        return $result;
    }
}
