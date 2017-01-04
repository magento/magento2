<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\MessageGenerators;

use Magento\Framework\DataObject;
use Magento\Signifyd\Model\MessageGeneratorException;
use Magento\Signifyd\Model\MessageGeneratorInterface;

/**
 * Generates message based on Signifyd case review disposition.
 */
class CaseReview implements MessageGeneratorInterface
{
    /**
     * @inheritdoc
     */
    public function generate(DataObject $data)
    {
        if (empty($data->getData('reviewDisposition'))) {
            throw new MessageGeneratorException(__('The "%1" should not be empty.', 'reviewDisposition'));
        }

        return __(
            'Case Update: Case Review was completed. Review Deposition is %1.',
            __($data->getData('reviewDisposition'))
        );
    }
}
