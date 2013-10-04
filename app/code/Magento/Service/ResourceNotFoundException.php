<?php
/**
 * Services must throw this exception when not able to locate a resource including lookup failure
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Service;

class ResourceNotFoundException extends \Magento\Service\Exception
{
    /**
     * Create custom message for resource not found exception.
     *
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     * @param array $parameters
     * @param string|int|null $resourceId
     */
    public function __construct(
        $message = '',
        // TODO Specify default exception code when Service \Exception Handling policy is defined
        $code = 0,
        \Exception $previous = null,
        $parameters = array(),
        $resourceId = null
    ) {
        if ($resourceId) {
            $parameters = array_merge($parameters, array('resource_id' => $resourceId));
            if (!$message) {
                $message = "Resource with ID '{$resourceId}' not found.";
            }
        }
        parent::__construct($message, $code, $previous, $parameters);
    }
}
