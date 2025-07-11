<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Model;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Element\Template\File\Resolver;
use Magento\ProductAlert\Block\Email\Stock;
use Magento\Store\Model\StoreManagerInterface;

/**
 *  Check before get template file name consists of themeId, if not add the themeId to get proper theme
 */
class UpdateThemeParams
{
    /**
     * UpdateThemeParams constructor
     *
     * @param DesignInterface $design
     * @param StoreManagerInterface $storeManager
     * @param Stock $stock
     */
    public function __construct(
        private readonly DesignInterface $design,
        private readonly StoreManagerInterface $storeManager,
        private readonly Stock $stock
    ) {
    }

    /**
     * Update theme params for multi store email templates
     *
     * @param Resolver $subject
     * @param string|null $template
     * @param array $params
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws NoSuchEntityException
     */
    public function beforeGetTemplateFileName(
        Resolver $subject,
        string|null $template,
        array $params = []
    ): array {
        if ($template === $this->stock->getTemplate() && !isset($params['themeId'])) {
            $params['themeId'] = $this->design->getConfigurationDesignTheme(
                Area::AREA_FRONTEND,
                ['store' => $this->storeManager->getStore()->getId()]
            );
        }
        return [$template, $params];
    }
}
