<?php
/**
 * URL Rewrite Option Provider
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
namespace Magento\UrlRewrite\Model\UrlRewrite;

use Magento\Framework\Option\ArrayInterface;

class OptionProvider implements ArrayInterface
{
    const TEMPORARY = 'R';

    const PERMANENT = 'RP';

    /**
     * @var array|null
     */
    protected $_options = null;

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                '' => __('No'),
                self::TEMPORARY => __('Temporary (302)'),
                self::PERMANENT => __('Permanent (301)'),
            );
        }
        return $this->_options;
    }

    /**
     * Get options list (redirects only)
     *
     * @return string[]
     */
    public function getRedirectOptions()
    {
        return array(self::TEMPORARY, self::PERMANENT);
    }

    /**
     * Return option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
