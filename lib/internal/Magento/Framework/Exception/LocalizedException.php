<?php
/**
 * Localized Exception
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;
use Magento\Framework\Phrase\Renderer\Placeholder;

class LocalizedException extends \Exception
{
    /**
     * @var \Magento\Framework\Phrase\Renderer\Placeholder
     */
    private static $renderer;

    /**
     * @var \Magento\Framework\Phrase
     */
    private $phrase;

    /**
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     */
    public function __construct(Phrase $phrase, \Exception $cause = null)
    {
        $this->phrase = $phrase;
        parent::__construct($phrase->render(), 0, $cause);
    }

    /**
     * Get the un-processed message, without the parameters filled in
     *
     * @return string
     */
    public function getRawMessage()
    {
        return $this->phrase->getText();
    }

    /**
     * Returns the array of parameters in the message
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->phrase->getArguments();
    }

    /**
     * Get the un-localized message, but with the parameters filled in
     *
     * @return string
     */
    public function getLogMessage()
    {
        if (!self::$renderer) {
            self::$renderer = new Placeholder();
        }
        return self::$renderer->render([$this->getRawMessage()], $this->getParameters());
    }
}
