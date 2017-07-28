<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Template\Css;

use Magento\Framework\View\Asset\NotationResolver\Variable;
use Magento\Framework\View\Asset\Repository;

/**
 * Class for processing css placeholders
 * @since 2.2.0
 */
class Processor
{
    /**
     * @var Repository
     * @since 2.2.0
     */
    private $assetRepository;

    /**
     * @param Repository $assetRepository
     * @since 2.2.0
     */
    public function __construct(Repository $assetRepository)
    {
        $this->assetRepository = $assetRepository;
    }

    /**
     * Process css placeholders
     *
     * @param string $css
     * @return string
     * @since 2.2.0
     */
    public function process($css)
    {
        $matches = [];
        if (preg_match_all(Variable::VAR_REGEX, $css, $matches, PREG_SET_ORDER)) {
            $replacements = [];
            foreach ($matches as $match) {
                if (!isset($replacements[$match[0]])) {
                    $replacements[$match[0]] = $this->getPlaceholderValue($match[1]);
                }
            }
            $css = str_replace(array_keys($replacements), $replacements, $css);
        }
        return $css;
    }

    /**
     * Retrieve placeholder value
     *
     * @param string $placeholder
     * @return string
     * @since 2.2.0
     */
    private function getPlaceholderValue($placeholder)
    {
        /** @var \Magento\Framework\View\Asset\File\FallbackContext $context */
        $context = $this->assetRepository->getStaticViewFileContext();

        switch ($placeholder) {
            case 'base_url_path':
                return $context->getBaseUrl();
            case 'locale':
                return $context->getLocale();
            default:
                return '';
        }
    }
}
