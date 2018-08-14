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
 */
class DeployTranslationsDictionary
{
    /**
     * @var JsTranslationConfig
     */
    private $jsTranslationConfig;

    /**
     * @var DeployStaticFile
     */
    private $deployStaticFile;

    /**
     * @var State
     */
    private $state;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param JsTranslationConfig $jsTranslationConfig
     * @param DeployStaticFile $deployStaticFile
     * @param State $state
     * @param LoggerInterface $logger
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
                        'locale' => $locale,
                        'replace' => true
                    ]
                );
            });
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
