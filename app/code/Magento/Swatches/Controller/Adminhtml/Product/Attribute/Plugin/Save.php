<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Controller\Adminhtml\Product\Attribute\Plugin;

use Magento\Catalog\Controller\Adminhtml\Product\Attribute;
use Magento\Framework\App\RequestInterface;
use Magento\Swatches\Model\Swatch;

/**
 * Plugin for product attribute save controller.
 */
class Save
{
    /**
     * Performs the conversion of the frontend input value.
     *
     * @param Attribute\Save $subject
     * @param RequestInterface $request
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(Attribute\Save $subject, RequestInterface $request)
    {
        $data = $request->getPostValue();

        if (isset($data['frontend_input'])) {
            switch ($data['frontend_input']) {
                case 'swatch_visual':
                    $data[Swatch::SWATCH_INPUT_TYPE_KEY] = Swatch::SWATCH_INPUT_TYPE_VISUAL;
                    $data['frontend_input'] = 'select';
                    $request->setPostValue($data);
                    break;
                case 'swatch_text':
                    $data[Swatch::SWATCH_INPUT_TYPE_KEY] = Swatch::SWATCH_INPUT_TYPE_TEXT;
                    $data['use_product_image_for_swatch'] = 0;
                    $data['frontend_input'] = 'select';
                    $request->setPostValue($data);
                    break;
                case 'select':
                    $data[Swatch::SWATCH_INPUT_TYPE_KEY] = Swatch::SWATCH_INPUT_TYPE_DROPDOWN;
                    $data['frontend_input'] = 'select';
                    $request->setPostValue($data);
                    break;
            }
        }
        return [$request];
    }
}
