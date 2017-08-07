<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Service;

use Magento\Framework\App\State;
use Magento\Framework\Translate\Js\Config as JsTranslationConfig;
use Psr\Log\LoggerInterface;

/**
 * Deploy translation dictionaries service
 * @since 2.2.0
 */
class DeployTranslationsDictionary
{
    /**
     * @var JsTranslationConfig
     * @since 2.2.0
     */
    private $jsTranslationConfig;

    /**
     * @var DeployStaticFile
     * @since 2.2.0
     */
    private $deployStaticFile;

    /**
     * @var State
     * @since 2.2.0
     */
    private $state;

    /**
     * @var LoggerInterface
     * @since 2.2.0
     */
    private $logger;

    /**
     * @param JsTranslationConfig $jsTranslationConfig
     * @param DeployStaticFile $deployStaticFile
     * @param State $state
     * @param LoggerInterface $logger
     * @since 2.2.0
     */
    public function __construct(
        JsTranslationConfig $jsTranslationConfig,
        DeployStaticFile $deployStaticFile,
        State $state,
        LoggerInterface $logger
    ) {
        $this->jsTranslationConfig = $jsTranslationConfig;
        $this->deployStaticFile = $deployStaticFile;
        $this->state = $state;
        $this->logger = $logger;
    }

    /**
     * @param string $area
     * @param string $theme
     * @param string $locale
     * @return void
     * @since 2.2.0
     */
    public function deploy($area, $theme, $locale)
    {
        try {
            $this->state->emulateAreaCode($area, function () use ($area, $theme, $locale) {
                $this->deployStaticFile->deployFile(
                    $this->jsTranslationConfig->getDictionaryFileName(),
                    [
                        'fileName' => $this->jsTranslationConfig->getDictionaryFileName(),
                        'area' => $area,
                        'theme' => $theme,
                        'locale' => $locale
                    ]
                );
            });
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
