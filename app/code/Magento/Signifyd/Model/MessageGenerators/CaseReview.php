<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\MessageGenerators;

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
    public function generate(array $data)
    {
        if (empty($data['reviewDisposition'])) {
            throw new MessageGeneratorException(__('The "%1" should not be empty.', 'reviewDisposition'));
        }

        return __(
            'Case Update: Case Review was completed. Review Deposition is %1.',
            __($data['reviewDisposition'])
        );
    }
}
