<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Adminhtml\Form\Element\Address;

/**
 * Customer Address Widget Form File Element Block
 */
class File extends \Magento\Customer\Block\Adminhtml\Form\Element\File
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
