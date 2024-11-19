<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Model\Js;

use Exception;
use Magento\Framework\App\State;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\DirSearch;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Framework\Phrase\RendererInterface;
use Magento\Framework\View\Design\Theme\ThemePackageList;

/**
 * DataProvider for js translation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider implements DataProviderInterface
{
    /**
     * @param State $appState
     * @param Config $config
     * @param ReadFactory $fileReadFactory
     * @param RendererInterface $translate
     * @param ComponentRegistrar $componentRegistrar
     * @param DirSearch $dirSearch
     * @param ThemePackageList $themePackageList
     * @param Files|null $filesUtility
     */
    public function __construct(
        protected readonly State $appState,
        protected readonly Config $config,
        protected readonly ReadFactory $fileReadFactory,
        protected readonly RendererInterface $translate,
        ComponentRegistrar $componentRegistrar,
        DirSearch $dirSearch,
        ThemePackageList $themePackageList,
        protected ?Files $filesUtility = null
    ) {
        $this->filesUtility = (null !== $filesUtility) ?
            $filesUtility : new Files(
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
     * @throws Exception
     * @throws LocalizedException
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
            $read = $this->fileReadFactory->create($filePath[0], DriverPool::FILE);
            $content = $read->readAll();
            foreach ($this->getPhrases($content) as $phrase) {
                try {
                    $translatedPhrase = $this->translate->render([$phrase], []);
                    if ($phrase != $translatedPhrase) {
                        $dictionary[$phrase] = $translatedPhrase;
                    }
                } catch (Exception $e) {
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
     * @throws LocalizedException
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
