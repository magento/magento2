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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Controller;

use Magento\Framework\App\ResponseInterface;

abstract class AbstractResult implements ResultInterface
{
    /**
     * @var int
     */
    protected $httpResponseCode;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * Set response code to result
     *
     * @param int $httpCode
     * @return $this
     */
    public function setHttpResponseCode($httpCode)
    {
        $this->httpResponseCode = $httpCode;
        return $this;
    }

    /**
     * Set a header
     *
     * If $replace is true, replaces any headers already defined with that
     * $name.
     *
     * @param string $name
     * @param string $value
     * @param boolean $replace
     * @return $this
     */
    public function setHeader($name, $value, $replace = false)
    {
        $this->headers[] = [
            'name'    => $name,
            'value'   => $value,
            'replace' => $replace
        ];
        return $this;
    }

    /**
     * @param ResponseInterface $response
     * @return $this
     */
    protected function applyHttpHeaders(ResponseInterface $response)
    {
        if (!empty($this->httpResponseCode)) {
            $response->setHttpResponseCode($this->httpResponseCode);
        }

        if (!empty($this->headers)) {
            foreach ($this->headers as $headerData) {
                $response->setHeader($headerData['name'], $headerData['value'], $headerData['replace']);
            }
        }
        return $this;
    }

    /**
     * @param ResponseInterface $response
     * @return $this
     */
    abstract protected function render(ResponseInterface $response);

    /**
     * Render content
     *
     * @param ResponseInterface $response
     * @return $this
     */
    public function renderResult(ResponseInterface $response)
    {
        $this->applyHttpHeaders($response);
        return $this->render($response);
    }
}
