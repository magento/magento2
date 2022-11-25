<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Adminhtml\Form\Element\Address;

/**
 * Customer Address Widget Form Image Element Block
 */
class Image extends \Magento\Customer\Block\Adminhtml\Form\Element\Image
{
    /**
     * @inheritdoc
     */
    protected function _getPreviewUrl()
    {
        return $this->_adminhtmlData->getUrl(
            'customer/address/viewfile',
            ['file' => $this->urlEncoder->encode($this->getValue())]
        );
    }
}
