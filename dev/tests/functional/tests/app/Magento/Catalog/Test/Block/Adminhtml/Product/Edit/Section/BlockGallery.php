<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Ui\Test\Block\Adminhtml\Section;

/**
 * Class for product gallery block.
 */
class BlockGallery extends Section
{
    /**
     * Selector for image loader container.
     *
     * @var string
     */
    private $imageLoader = '.image.image-placeholder .file-row';

    /**
     * Selector for image upload input.
     *
     * @var string
     */
    private $imageUploadInput = '[name="image"]';

    /**
     * Upload product images.
     *
     * @param array $data
     * @param SimpleElement|null $element
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setFieldsData(array $data, SimpleElement $element = null)
    {
        foreach ($data['image']['value'] as $imageData) {
            $uploadElement = $element->find($this->imageUploadInput, Locator::SELECTOR_CSS, 'upload');
            $uploadElement->setValue($imageData['file']);
            $this->waitForElementNotVisible($this->imageLoader);
        }
        return $this;
    }
}
