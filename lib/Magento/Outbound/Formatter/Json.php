<?php
/**
 * Formatter that converts an array into JSON string.
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
 * @category    Magento
 * @package     Magento_Outbound
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Outbound\Formatter;

class Json implements \Magento\Outbound\FormatterInterface
{
    /**
     * The value for the Content-Type header for messages containing a JSON body
     */
    const CONTENT_TYPE = 'application/json';

    /**
     * Format the body of a message into JSON
     *
     * @param array $body
     * @throws \LogicException
     * @return string formatted body
     */
    public function format(array $body)
    {
        $formattedData = json_encode($body);
        if (false === $formattedData) {
            throw new \LogicException('The data provided cannot be converted to JSON.');
        }
        return $formattedData;
    }

    /**
     * Returns the content type for JSON formatting
     *
     * @return string 'application/json'
     */
    public function getContentType()
    {
        return self::CONTENT_TYPE;
    }
}
