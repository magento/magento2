<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Exception;

use Magento\Framework\Phrase\Renderer\Placeholder;

class ErrorMessage
{
    /**
     * The error message.
     *
     * @var string
     */
    private $message;

    /**
     * The message substitution parameters.
     *
     * @var array
     */
    private $params;

    /**
     * The renderer to use for retrieving the log-compatible message.
     *
     * @var Placeholder
     */
    private $renderer;

    /**
     * Initialize the error message object.
     *
     * @param string $message Error message
     * @param array $parameters Message arguments (i.e. substitution parameters)
     */
    public function __construct($message, array $parameters = [])
    {
        $this->message = $message;
        $this->params = $parameters;
        $this->renderer = new Placeholder();
    }

    /**
     * Return the parameters associated with this error.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->params;
    }

    /**
     * Return the message localized to based on the locale of the current request.
     *
     * @return string
     */
    public function getMessage()
    {
        return __($this->message, $this->params);
    }

    /**
     * Return the un-processed message, which can be used as a localization key by web service clients.
     *
     * @return string
     */
    public function getRawMessage()
    {
        return $this->message;
    }

    /**
     * Return the un-localized string, but with the parameters filled in.
     *
     * @return string
     */
    public function getLogMessage()
    {
        return $this->renderer->render([$this->message], $this->params);
    }
}
