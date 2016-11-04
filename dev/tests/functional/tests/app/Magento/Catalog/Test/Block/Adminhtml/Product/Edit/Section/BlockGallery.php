<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Ui\Test\Block\Adminhtml\Section;

class BlockGallery extends Section
{
    /**
     * Upload product images
     *
     * @param array $data
     * @param SimpleElement|null $element
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setFieldsData(array $data, SimpleElement $element = null)
    {
        if (isset($data['image'])) {
            foreach ($data['image']['value'] as $key => $imageData) {
                $uploadElement = $this->_rootElement->find('[name="image"]', Locator::SELECTOR_CSS, 'upload');
                $uploadElement->setValue($imageData['file']);
                $this->waitForElementNotVisible('.image.image-placeholder .file-row');
            }
        }
        return $this;
    }
}
