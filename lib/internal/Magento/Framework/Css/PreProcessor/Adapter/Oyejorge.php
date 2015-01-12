<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor\Adapter;

use Magento\Framework\App\State;

/**
 * Oyejorge adapter model
 */
class Oyejorge implements \Magento\Framework\Css\PreProcessor\AdapterInterface
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(State $appState)
    {
        $this->appState = $appState;
    }

    /**
     * @param string $sourceFilePath
     * @return string
     */
    public function process($sourceFilePath)
    {
        $options = ['relativeUrls' => false, 'compress' => $this->appState->getMode() !== State::MODE_DEVELOPER];
        $parser = new \Less_Parser($options);
        $parser->parseFile($sourceFilePath, '');
        return $parser->getCss();
    }
}
