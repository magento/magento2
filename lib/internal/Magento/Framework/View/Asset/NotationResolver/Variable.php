<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\NotationResolver;

use Magento\Framework\View\Asset;

/**
 * Variable resolver to allow specific placeholders in CSS files
 */
class Variable
{
    /**
     * Regex matching {{placeholders}}
     */
    const VAR_REGEX = '/{{([_a-z]*)}}/si';

    /**
     * Provides the combined base url and base path from the asset context
     */
    const VAR_BASE_URL_PATH = 'base_url_path';

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @param Asset\Repository $assetRepo
     */
    public function __construct(Asset\Repository $assetRepo)
    {
        $this->assetRepo = $assetRepo;
    }

    /**
     * Replaces the placeholder variables into the given path
     *
     * @param string $path
     * @return string
     */
    public function convertVariableNotation($path)
    {
        $matches = [];
        if (preg_match_all(self::VAR_REGEX, $path, $matches, PREG_SET_ORDER)) {
            $replacements = [];
            foreach ($matches as $match) {
                if (!isset($replacements[$match[0]])) {
                    $replacements[$match[0]] = $this->getPlaceholderValue($match[1]);
                }
            }
            $path = str_replace(array_keys($replacements), $replacements, $path);
        }
        return $path;
    }

    /**
     * Process placeholder
     *
     * @param string $placeholder
     * @return string
     */
    public function getPlaceholderValue($placeholder)
    {
        /** @var \Magento\Framework\View\Asset\File\FallbackContext $context */
        $context = $this->assetRepo->getStaticViewFileContext();

        switch ($placeholder) {
            case self::VAR_BASE_URL_PATH:
                return '{{' . self::VAR_BASE_URL_PATH . '}}' . $context->getAreaCode() .
                    ($context->getThemePath() ? '/' . $context->getThemePath() . '/' : '') .
                    '{{locale}}';
            default:
                return '';
        }
    }
}
