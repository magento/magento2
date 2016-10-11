<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

/**
 * Class ValidatorResultMerger
 */
class ValidatorResultMerger
{
    /**
     * @var ValidatorResultInterfaceFactory
     */
    private $validatorResultInterfaceFactory;

    /**
     * ValidatorResultMerger constructor.
     *
     * @param ValidatorResultInterfaceFactory $validatorResultInterfaceFactory
     */
    public function __construct(ValidatorResultInterfaceFactory $validatorResultInterfaceFactory)
    {
        $this->validatorResultInterfaceFactory = $validatorResultInterfaceFactory;
    }

    /**
     * Merges two validator results and additional messages.
     *
     * @param ValidatorResultInterface $first
     * @param ValidatorResultInterface $second
     * @param array $others
     *
     * @return ValidatorResultInterface
     */
    public function merge(ValidatorResultInterface $first, ValidatorResultInterface $second, array $others = [])
    {
        $messages = array_merge($first->getMessages(), $second->getMessages());

        foreach ($others as $messagesBunch) {
            $messages = array_merge($messages, $messagesBunch);
        }

        /** @var ValidatorResultInterface $result */
        $result = $this->validatorResultInterfaceFactory->create();

        foreach ($messages as $message) {
            $result->addMessage($message);
        }

        return $result;
    }
}
