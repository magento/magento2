<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\MessageGenerators;

/**
 * Represents common abstraction for Signifyd Case/Guarantee messages.
 * Each interface implementation might use Case/Guarantee data to generate specific message.
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
