<?php
/**
 * Localized Exception
 *
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

class LocalizedException extends \Exception
{
    /** @var array */
    protected $params = [];

    /** @var string */
    protected $rawMessage;

    /** @var Placeholder */
    private $renderer;

    /**
     * @param string     $message
     * @param array      $params
     * @param \Exception $cause
     */
    public function __construct($message, array $params = [], \Exception $cause = null)
    {
        $this->params = $params;
        $this->rawMessage = $message;
        $this->renderer = new Placeholder();

        parent::__construct(__($message, $params), 0, $cause);
    }

    /**
     * Get the un-processed message, without the parameters filled in
     *
     * @return string
     */
    public function getRawMessage()
    {
        return $this->rawMessage;
    }

    /**
     * Get the un-localized message, but with the parameters filled in
     *
     * @return string
     */
    public function getLogMessage()
    {
        return $this->renderer->render([$this->rawMessage], $this->params);
    }

    /**
     * Returns the array of parameters in the message
     *
     * @return array Parameter name => values
     */
    public function getParameters()
    {
        return $this->params;
    }
}
