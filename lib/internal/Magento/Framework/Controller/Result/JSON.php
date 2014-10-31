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

namespace Magento\Framework\Controller\Result;

use Magento\Framework\Controller\AbstractResult;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Translate\InlineInterface;

/**
 * A possible implementation of JSON response type (instead of hardcoding json_encode() all over the place)
 * Actual for controller actions that serve ajax requests
 */
class JSON extends AbstractResult
{
    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $translateInline;

    /**
     * @var string
     */
    protected $json;

    /**
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     */
    public function __construct(InlineInterface $translateInline)
    {
        $this->translateInline = $translateInline;
    }

    /**
     * Set json data
     *
     * @param mixed $data
     * @param boolean $cycleCheck Optional; whether or not to check for object recursion; off by default
     * @param array $options Additional options used during encoding
     * @return $this
     */
    public function setData($data, $cycleCheck = false, $options = array())
    {
        $this->json = \Zend_Json::encode($data, $cycleCheck, $options);
        return $this;
    }

    /**
     * @param string $jsonData
     * @return $this
     */
    public function setJsonData($jsonData)
    {
        $this->json = (string)$jsonData;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function render(ResponseInterface $response)
    {
        $this->translateInline->processResponseBody($this->json, true);
        $response->representJson($this->json);
        return $this;
    }
}
