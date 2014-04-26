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
namespace Magento\Usps\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Usps data helper
 */
class Data extends AbstractHelper
{
    /**
     * Available shipping methods
     *
     * @var array
     */
    protected $availableShippingMethods = array(
        'usps_0_FCLE', // First-Class Mail Large Envelope
        'usps_1',      // Priority Mail
        'usps_2',      // Priority Mail Express Hold For Pickup
        'usps_3',      // Priority Mail Express
        'usps_4',      // Standard Post
        'usps_6',      // Media Mail
        'usps_INT_1',  // Priority Mail Express International
        'usps_INT_2',  // Priority Mail International
        'usps_INT_4',  // Global Express Guaranteed (GXG)
        'usps_INT_7',  // Global Express Guaranteed Non-Document Non-Rectangular
        'usps_INT_8',  // Priority Mail International Flat Rate Envelope
        'usps_INT_9',  // Priority Mail International Medium Flat Rate Box
        'usps_INT_10', // Priority Mail Express International Flat Rate Envelope
        'usps_INT_11', // Priority Mail International Large Flat Rate Box
        'usps_INT_12', // USPS GXG Envelopes
        'usps_INT_14', // First-Class Mail International Large Envelope
        'usps_INT_16', // Priority Mail International Small Flat Rate Box
        'usps_INT_20', // Priority Mail International Small Flat Rate Envelope
        'usps_INT_26', // Priority Mail Express International Flat Rate Boxes
    );

    /**
     * Define if we need girth parameter in the package window
     *
     * @param string $shippingMethod
     * @return bool
     */
    public function displayGirthValue($shippingMethod)
    {
        return in_array($shippingMethod, $this->availableShippingMethods);
    }
}
