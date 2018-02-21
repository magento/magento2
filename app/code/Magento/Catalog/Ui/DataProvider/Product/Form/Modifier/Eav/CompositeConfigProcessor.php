<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;

use Psr\Log\LoggerInterface as Logger;

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
     * @param array $eavWysiwygDataProcessors
     */
    public function __construct(Logger $logger, array $eavWysiwygDataProcessors)
    {
        $this->logger = $logger;
        $this->eavWysiwygDataProcessors = $eavWysiwygDataProcessors;
    }


    /**
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @return array
     */
    public function process(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute)
    {
        $wysiwygConfigData = [];

        foreach ($this->eavWysiwygDataProcessors as $processor) {
            if (!$processor instanceof WysiwygConfigDataProcessorInterface) {
                $this->logger->critical(
                    __('Processor %1 doesn\'t implement BaseSelectProcessorInterface. It will be skipped',
                        get_class($processor))
                );
                continue;
            }

            //need to move to composite provider
            $wysiwygConfigData = array_merge_recursive($wysiwygConfigData, $processor->process($attribute));
        }

        return $wysiwygConfigData;
    }
}
