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
 * @since 2.0.0
 */
class LocalizedException extends \Exception
{
    /**
     * @var \Magento\Framework\Phrase
     * @since 2.0.0
     */
    protected $phrase;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $logMessage;

    /**
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     * @since 2.0.0
     */
    public function __construct(Phrase $phrase, \Exception $cause = null, $code = 0)
    {
        $this->phrase = $phrase;
        parent::__construct($phrase->render(), intval($code), $cause);
    }

    /**
     * Get the un-processed message, without the parameters filled in
     *
     * @return string
     * @since 2.0.0
     */
    public function getRawMessage()
    {
        return $this->phrase->getText();
    }

    /**
     * Get parameters, corresponding to placeholders in raw exception message
     *
     * @return array
     * @since 2.0.0
     */
    public function getParameters()
    {
        return $this->phrase->getArguments();
    }

    /**
     * Get the un-localized message, but with the parameters filled in
     *
     * @return string
     * @since 2.0.0
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
