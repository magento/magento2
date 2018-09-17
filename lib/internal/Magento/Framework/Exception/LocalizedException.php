<?php
/**
 * Localized Exception
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;
use Magento\Framework\Phrase\Renderer\Placeholder;

/**
 * @api
 */
class LocalizedException extends \Exception
{
    /**
     * @var \Magento\Framework\Phrase
     */
    protected $phrase;

    /**
     * @var string
     */
    protected $logMessage;

    /**
     * Constructor
     *
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
     * Get parameters, corresponding to placeholders in raw exception message
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
        if ($this->logMessage === null) {
            $renderer = new Placeholder();
            $this->logMessage = $renderer->render([$this->getRawMessage()], $this->getParameters());
        }
        return $this->logMessage;
    }
}
