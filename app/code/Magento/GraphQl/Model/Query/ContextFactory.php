<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Query;

use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

/**
 * @inheritdoc
 */
class ContextFactory implements ContextFactoryInterface
{
    /**
     * @var ExtensionAttributesFactory
     */
    private $extensionAttributesFactory;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ContextParametersProcessorInterface[]
     */
    private $contextParametersProcessors;

    /**
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     * @param ObjectManagerInterface $objectManager
     * @param ContextParametersProcessorInterface[] $contextParametersProcessors
     */
    public function __construct(
        ExtensionAttributesFactory $extensionAttributesFactory,
        ObjectManagerInterface $objectManager,
        array $contextParametersProcessors = []
    ) {
        $this->extensionAttributesFactory = $extensionAttributesFactory;
        $this->objectManager = $objectManager;
        $this->contextParametersProcessors = $contextParametersProcessors;
    }

    /**
     * @inheritdoc
     */
    public function create(): ContextInterface
    {
        $contextParameters = $this->objectManager->create(ContextParametersInterface::class);

        foreach ($this->contextParametersProcessors as $contextParametersProcessor) {
            if (!$contextParametersProcessor instanceof ContextParametersProcessorInterface) {
                throw new LocalizedException(
                    __('ContextParametersProcessors must implement %1', ContextParametersProcessorInterface::class)
                );
            }
            $contextParameters = $contextParametersProcessor->execute($contextParameters);
        }

        $extensionAttributes = $this->extensionAttributesFactory->create(
            ContextInterface::class,
            [
                'data' => $contextParameters->getExtensionAttributesData(),
            ]
        );

        $context = $this->objectManager->create(
            ContextInterface::class,
            [
                'userType' => $contextParameters->getUserType(),
                'userId' => $contextParameters->getUserId(),
                'extensionAttributes' => $extensionAttributes,
            ]
        );
        return $context;
    }
}
