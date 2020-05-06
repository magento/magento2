<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\MessageGenerators;

/**
 * Represents common abstraction for Signifyd Case/Guarantee messages.
 * Each interface implementation might use Case/Guarantee data to generate specific message.
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
 */
interface GeneratorInterface
{
    /**
     * Creates new localized message based on Signifyd Case/Guarantee data.
     * @param array $data
     * @return \Magento\Framework\Phrase
     * @throws GeneratorException
     */
    public function generate(array $data);
}
