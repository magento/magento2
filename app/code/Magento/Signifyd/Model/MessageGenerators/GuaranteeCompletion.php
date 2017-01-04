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
 * Generates message based on Signifyd Guarantee disposition.
 */
class GuaranteeCompletion implements MessageGeneratorInterface
{
    /**
     * @inheritdoc
     */
    public function generate(DataObject $data)
    {
        if (empty($data->getData('guaranteeDisposition'))) {
            throw new MessageGeneratorException(__('The "%1" should not be empty.', 'guaranteeDisposition'));
        }

        return __('Case Update: Guarantee Disposition is %1.', __($data->getData('guaranteeDisposition')));
    }
}
