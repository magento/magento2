<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductPage;

/**
 * Verify displayed images on product page are correct.
 */
class AssertConfigurableProductImages extends AssertProductPage
{
    /**
     * Displayed images.
     *
     * @var array
     */
    private $displayedImages = [];

    /**
     * Verify displayed images on product page are correct.
     *
     * @return array
     */
    protected function verify()
    {
        $errors = [];
        $errors[] = $this->verifyBaseImage();
        $errors[] = $this->verifyOptionsImages();

        return array_filter($errors);
    }

    /**
     * Verify correct base image is shown.
     *
     * @return null|string
     */
    private function verifyBaseImage()
    {
        $message = null;
        $data = $this->product->getData();

        $displayedImage = $this->productView->getBaseImageSource();
        $this->displayedImages[] = $displayedImage;

        if ($this->areImagesDifferent($displayedImage, $data['image'][0]['file'])) {
            $message = 'Product image is not correct.';
        }

        return $message;
    }

    /**
     * Verify displayed options images on product page are different.
     *
     * @return string|null
     */
    protected function verifyOptionsImages()
    {
        $message = null;
        $configurableAttributes = $this->product->getData('configurable_attributes_data')['attributes_data'];
        $attribute = array_shift($configurableAttributes);
        $customOptions = [];

        foreach ($attribute['options'] as $option) {
            $customOptions[] = [
                'type' => $attribute['frontend_input'],
                'title' => $attribute['frontend_label'],
                'value' => $option['label']
            ];
        }

        foreach ($customOptions as $customOption) {
            $this->productView->getCustomOptionsBlock()->fillCustomOptions([$customOption]);
            $displayedImage = $this->productView->getBaseImageSource();
            if (in_array($displayedImage, $this->displayedImages)) {
                $message = 'Option image is not correct.';
                break;
            }

            $this->displayedImages[] = $displayedImage;
        }

        return $message;
    }

    /**
     * Compare images and return true if they are different.
     *
     * @param string $compared
     * @param string $toCompare
     * @return bool
     */
    private function areImagesDifferent($compared, $toCompare)
    {
        preg_match('`/(\w*?)\.(\w*?)$`', $compared, $shownImage);
        preg_match('`/(\w*?)\.(\w*?)$`', $toCompare, $expectedImage);

        return strpos($shownImage[1], $expectedImage[1]) === false || $expectedImage[2] !== $shownImage[2];
    }
}
