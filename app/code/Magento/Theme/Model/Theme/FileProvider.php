<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme;

use Magento\Framework\View\Design\Theme\File\CollectionInterface;
use Magento\Framework\View\Design\Theme\FileProviderInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\ResourceModel\Theme\File\CollectionFactory;

class FileProvider implements FileProviderInterface
{
    /**
     * @param CollectionFactory $fileFactory
     */
    public function __construct(
        protected readonly CollectionFactory $fileFactory
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(ThemeInterface $theme, array $filters = [])
    {
        /** @var CollectionInterface $themeFiles */
        $themeFiles = $this->fileFactory->create();
        $themeFiles->addThemeFilter($theme);
        foreach ($filters as $field => $value) {
            $themeFiles->addFieldToFilter($field, $value);
        }
        $themeFiles->setDefaultOrder();
        return $themeFiles->getItems();
    }
}
