<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Controller\Adminhtml\Product\Attribute\Plugin;

use Magento\Catalog\Controller\Adminhtml\Product\Attribute;
use Magento\Framework\App\RequestInterface;
use Magento\Swatches\Model\ConvertSwatchAttributeFrontendInput;

/**
 * Plugin for product attribute save controller.
 */
class Save
{
    /**
     * @var ConvertSwatchAttributeFrontendInput
     */
    private $convertSwatchAttributeFrontendInput;

    /**
     * @param ConvertSwatchAttributeFrontendInput $convertSwatchAttributeFrontendInput
     */
    public function __construct(
        ConvertSwatchAttributeFrontendInput $convertSwatchAttributeFrontendInput
    ) {
        $this->convertSwatchAttributeFrontendInput = $convertSwatchAttributeFrontendInput;
    }

    /**
     * Performs the conversion of the frontend input value.
     *
     * @param Attribute\Save $subject
     * @param RequestInterface $request
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(Attribute\Save $subject, RequestInterface $request): array
    {
        $data = $request->getPostValue();
        $data = $this->convertSwatchAttributeFrontendInput->execute($data);
        $request->setPostValue($data);

        return [$request];
    }
}
