<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Model\Js;

use Magento\Framework\Exception\LocalizedException;

/**
 * DataProvider for js translation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider implements DataProviderInterface
{
    /**
     * Application state
     *
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * Js translation configuration
     *
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Utility\Files
     */
    protected $filesUtility;

    /**
     * Filesystem
     *
     * @var \Magento\Framework\Filesystem\File\ReadFactory
     */
    protected $fileReadFactory;

    /**
     * Basic translate renderer
     *
     * @var \Magento\Framework\Phrase\RendererInterface
     */
    protected $translate;

    /**
     * @param \Magento\Framework\App\State $appState
     * @param Config $config
     * @param \Magento\Framework\Filesystem\File\ReadFactory $fileReadFactory
     * @param \Magento\Framework\Phrase\RendererInterface $translate
     * @param \Magento\Framework\Component\ComponentRegistrar $componentRegistrar
     * @param \Magento\Framework\Component\DirSearch $dirSearch
     * @param \Magento\Framework\View\Design\Theme\ThemePackageList $themePackageList
     * @param \Magento\Framework\App\Utility\Files|null $filesUtility
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        Config $config,
        \Magento\Framework\Filesystem\File\ReadFactory $fileReadFactory,
        \Magento\Framework\Phrase\RendererInterface $translate,
        \Magento\Framework\Component\ComponentRegistrar $componentRegistrar,
        \Magento\Framework\Component\DirSearch $dirSearch,
        \Magento\Framework\View\Design\Theme\ThemePackageList $themePackageList,
        \Magento\Framework\App\Utility\Files $filesUtility = null
    ) {
        $this->appState = $appState;
        $this->config = $config;
        $this->fileReadFactory = $fileReadFactory;
        $this->translate = $translate;
        $this->filesUtility = (null !== $filesUtility) ?
            $filesUtility : new \Magento\Framework\App\Utility\Files(
                $componentRegistrar,
                $dirSearch,
                $themePackageList
            );
    }

    /**
     * Get translation data
     *
     * @param string $themePath
     * @return array
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getData($themePath)
    {
        $areaCode = $this->appState->getAreaCode();

        $files = array_merge(
            $this->filesUtility->getJsFiles('base', $themePath),
            $this->filesUtility->getJsFiles($areaCode, $themePath),
            $this->filesUtility->getStaticHtmlFiles('base', $themePath),
            $this->filesUtility->getStaticHtmlFiles($areaCode, $themePath)
        );

        $dictionary = [];
        foreach ($files as $filePath) {
            $read = $this->fileReadFactory->create($filePath[0], \Magento\Framework\Filesystem\DriverPool::FILE);
            $content = $read->readAll();
            foreach ($this->getPhrases($content) as $phrase) {
                try {
                    $translatedPhrase = $this->translate->render([$phrase], []);
                    if ($phrase != $translatedPhrase) {
                        $dictionary[$phrase] = $translatedPhrase;
                    }
                } catch (\Exception $e) {
                    throw new LocalizedException(
                        __('Error while translating phrase "%s" in file %s.', $phrase, $filePath[0]),
                        $e
                    );
                }
            }
        }

        ksort($dictionary);

        return $dictionary;
    }

    /**
     * Parse content for entries to be translated
     *
     * @param string $content
     * @return string[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getPhrases($content)
    {
        $phrases = [];
        foreach ($this->config->getPatterns() as $pattern) {
            $concatenatedContent = preg_replace('~(["\'])\s*?\+\s*?\1~', '', $content);
            $result = preg_match_all($pattern, $concatenatedContent, $matches);

            if ($result) {
                if (isset($matches[2])) {
                    foreach ($matches[2] as $match) {
                        $phrases[] = $match !== null ? str_replace(["\'", '\"'], ["'", '"'], $match) : '';
                    }
                }
            }
            if (false === $result) {
                throw new LocalizedException(
                    __('Error while generating js translation dictionary: "%s"', error_get_last())
                );
            }
        }
        return $phrases;
    }
}
