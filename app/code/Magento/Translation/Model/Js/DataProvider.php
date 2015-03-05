<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Model\Js;

use Magento\Framework\Test\Utility\Files;
use Magento\Framework\App\State;

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
     * @param State $appState
     */
    public function __construct(State $appState, Config $config)
    {
        $this->appState = $appState;
        $this->config = $config;
        $this->filesUtility = new Files(BP);
    }

    /**
     * Get translation data
     *
     * @param string $themePath
     * @return string[]
     * @throws \Exception
     * @throws \Magento\Framework\Exception
     */
    public function getData($themePath)
    {
        $dictionary = [];

        $files = $this->filesUtility->getJsFiles($this->appState->getAreaCode(), $themePath);
        foreach ($files as $filePath) {
            foreach ($this->getPhrases(file_get_contents($filePath[0])) as $phrase) {
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
