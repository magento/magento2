<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryIntegration\Plugin;

use Magento\MediaGalleryUiApi\Api\ConfigInterface;
use Magento\Ui\Component\Form\Element\DataType\Media\OpenDialogUrl;

class NewMediaGalleryOpenDialogUrl
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param OpenDialogUrl $subject
     * @param string $result
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return string
     */
    public function afterGet(OpenDialogUrl $subject, string $result)
    {
        return $this->config->isEnabled() ? 'media_gallery/index/index' : $result;
    }
}
