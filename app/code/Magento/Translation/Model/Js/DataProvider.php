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
     * @var Files
     */
    protected $filesUtility;

    /**
     * @var State
     */
    protected $appState;

    /**
     * @param State $appState
     */
    public function __construct(State $appState)
    {
        $this->appState = $appState;
        $this->filesUtility = new \Magento\Framework\Test\Utility\Files(BP);
    }

    /**
     * Get translation data
     */
    public function getData()
    {
        $dictionary = [];

        $files = $this->filesUtility->getJsFilesForArea($this->appState->getAreaCode());
        foreach ($files as $filePath) {
            $content = file_get_contents($filePath);
            foreach ($this->getPhrases($content) as $phrase) {
                $dictionary[$phrase] = __($phrase);
            }
        }

        return $dictionary;
    }

    /**
     * @param string $content
     * @return string[]
     * @throws \Exception
     */
    protected function getPhrases($content)
    {
        $result = preg_match_all(Config::TRANSLATION_CALL_PATTERN, $content, $matches);

        if ($result) {
            return $matches[1];
        }
        if (false === $result) {
            throw new \Exception(
                sprintf('Error while generating js translation dictionary: "%s"', error_get_last())
            );
        }

        return [];
    }
}
