<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Model\Js;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * DataProvider for js translation
 */
class DataProvider implements DataProviderInterface
{
    /**
     * Application state
     *
     * @var State
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
     * @var Files
     */
    protected $filesUtility;

    /**
     * Filesystem
     *
     * @var ReadInterface
     */
    protected $rootDirectory;

    /**
     * @param State $appState
     * @param Config $config
     * @param Filesystem $filesystem
     * @param Files $filesUtility
     */
    public function __construct(State $appState, Config $config, Filesystem $filesystem, Files $filesUtility = null)
    {
        $this->appState = $appState;
        $this->config = $config;
        $this->rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->filesUtility = (null !== $filesUtility) ? $filesUtility : new Files(BP);
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
        $dictionary = [];

        $files = $this->filesUtility->getJsFiles($this->appState->getAreaCode(), $themePath);
        foreach ($files as $filePath) {
            $content = $this->rootDirectory->readFile($this->rootDirectory->getRelativePath($filePath[0]));
            foreach ($this->getPhrases($content) as $phrase) {
                $translatedPhrase = (string) __($phrase);
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
                $phrases = array_merge($phrases, $matches[1]);
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
