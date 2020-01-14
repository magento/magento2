<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;

use Psr\Log\LoggerInterface as Logger;

/**
 * Process config for Wysiwyg.
 */
class CompositeConfigProcessor implements WysiwygConfigDataProcessorInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $eavWysiwygDataProcessors = [];

    /**
     * CompositeConfigProcessor constructor.
     * @param Logger $logger
     * @param array $eavWysiwygDataProcessors
     */
    public function __construct(Logger $logger, array $eavWysiwygDataProcessors)
    {
        $this->logger = $logger;
        $this->eavWysiwygDataProcessors = $eavWysiwygDataProcessors;
    }

    /**
     * @inheritdoc
     */
    public function process(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute)
    {
        $wysiwygConfigData = [];

        foreach ($this->eavWysiwygDataProcessors as $processor) {
            if (!$processor instanceof WysiwygConfigDataProcessorInterface) {
                $this->logger->critical(
                    __(
                        'Processor %1 doesn\'t implement WysiwygConfigDataProcessorInterface. It will be skipped',
                        get_class($processor)
                    )
                );
                continue;
            }

            $wysiwygConfigData = array_merge_recursive($wysiwygConfigData, $processor->process($attribute));
        }

        return $wysiwygConfigData;
    }
}
