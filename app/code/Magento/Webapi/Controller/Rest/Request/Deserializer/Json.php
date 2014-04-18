<?php
/**
 * JSON deserializer of REST request content.
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
namespace Magento\Webapi\Controller\Rest\Request\Deserializer;

use \Magento\Framework\App\State;

class Json implements \Magento\Webapi\Controller\Rest\Request\DeserializerInterface
{
    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_helper;

    /**
     * @var State
     */
    protected $_appState;

    /**
     * @param \Magento\Core\Helper\Data $helper
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(\Magento\Core\Helper\Data $helper, State $appState)
    {
        $this->_helper = $helper;
        $this->_appState = $appState;
    }

    /**
     * Parse Request body into array of params.
     *
     * @param string $encodedBody Posted content from request.
     * @return array|null Return NULL if content is invalid.
     * @throws \InvalidArgumentException
     * @throws \Magento\Webapi\Exception If decoding error was encountered.
     */
    public function deserialize($encodedBody)
    {
        if (!is_string($encodedBody)) {
            throw new \InvalidArgumentException(
                sprintf('"%s" data type is invalid. String is expected.', gettype($encodedBody))
            );
        }
        try {
            $decodedBody = $this->_helper->jsonDecode($encodedBody);
        } catch (\Zend_Json_Exception $e) {
            if ($this->_appState->getMode() !== State::MODE_DEVELOPER) {
                throw new \Magento\Webapi\Exception(__('Decoding error.'));
            } else {
                throw new \Magento\Webapi\Exception(
                    __('Decoding error: %1%2%3%4', PHP_EOL, $e->getMessage(), PHP_EOL, $e->getTraceAsString())
                );
            }
        }
        return $decodedBody;
    }
}
