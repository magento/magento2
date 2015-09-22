<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Model\Js;

use Magento\Framework\Component\DirSearch;
use Magento\Framework\Phrase\Renderer\Translate;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * DataProvider for js translation
 *
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
     * Files utility
     *
     * @var \Magento\Framework\App\Utility\Files
     */
    protected $filesUtility;

    /**
     * Filesystem
     *
     * @var ReadInterface
     */
    protected $rootDirectory;

    /**
     * Basic translate renderer
     *
     * @var Translate
     */
    protected $translate;

    /**
     * @param \Magento\Framework\App\State $appState
     * @param Config $config
     * @param Filesystem $filesystem
     * @param Translate $translate
     * @param DirSearch $dirSearch
     * @param \Magento\Framework\App\Utility\Files $filesUtility
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        Config $config,
        Filesystem $filesystem,
        Translate $translate,
        DirSearch $dirSearch,
        \Magento\Framework\App\Utility\Files $filesUtility = null
    ) {
        $this->appState = $appState;
        $this->config = $config;
        $this->rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->translate = $translate;
        $this->filesUtility = (null !== $filesUtility) ?
            $filesUtility : new \Magento\Framework\App\Utility\Files(new ComponentRegistrar(), $dirSearch);
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
            $content = $this->rootDirectory->readFile($this->rootDirectory->getRelativePath($filePath[0]));
            foreach ($this->getPhrases($content) as $phrase) {
                $translatedPhrase = $this->translate->render([$phrase], []);
                if ($phrase != $translatedPhrase) {
                    $dictionary[$phrase] = $translatedPhrase;
                }
            }
        }

        return $dictionary;
    }

    /**
     * Parse content for entries to be translated
     *
     * @param string $content
     * @return string[]
     * @throws \Exception
     */
    protected function getPhrases($content)
    {
        $phrases = [];
        foreach ($this->config->getPatterns() as $pattern) {
            $result = preg_match_all($pattern, $content, $matches);

            if ($result) {
                if (isset($matches[2])) {
                    foreach ($matches[2] as $match) {
                        $phrases[] = $match;
                    }
                }
            }
            if (false === $result) {
                throw new \Exception(
                    sprintf('Error while generating js translation dictionary: "%s"', error_get_last())
                );
            }
        }
        return $phrases;
    }
}
