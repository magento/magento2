<?php
/**
 * {license_notice}
 *
 * @category   build
 * @package    license
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * AFL license information class
 *
 */
class Osl extends LicenseAbstract
{
    /**
     * Prepare short information about license
     *
     * @return string
     */
    public function getNotice()
    {
        return <<<EOT
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
EOT;

    }

    /**
     * Prepare data for phpdoc attribute "license"
     *
     * @return string
     */
    public function getLink()
    {
        return 'http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)';
    }
}
