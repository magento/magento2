<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

/**
 * Represents common abstraction for Signifyd Case/Guarantee messages.
 * Each interface implementation might use Case/Guarantee data to generate specific message.
 */
interface MessageGeneratorInterface
{
    /**
     * Creates new localized message based on Signifyd Case/Guarantee data.
     * @param $data
     * @return \Magento\Framework\Phrase
     * @throws MessageGeneratorException
     */
    public function generate(array $data);
}
