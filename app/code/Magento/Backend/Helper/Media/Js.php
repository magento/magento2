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
 * @category    Magento
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Helper\Media;

/**
 * Media library js helper
 *
 * @deprecated since 1.7.0.0
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Js extends \Magento\Core\Helper\Js
{
    /**
     * {@inheritdoc}
     */
    protected function _populateTranslateData()
    {
        $this->_addTranslation('Complete', __('Complete'));
        $this->_addTranslation(
            'The file size should be more than 0 bytes.',
            __('The file size should be more than 0 bytes.')
        );
        $this->_addTranslation('Upload Security Error', __('Upload Security Error'));
        $this->_addTranslation('Upload HTTP Error', __('Upload HTTP Error'));
        $this->_addTranslation('Upload I/O Error', __('Upload I/O Error'));
        $this->_addTranslation(
            'SSL Error: Invalid or self-signed certificate',
            __('SSL Error: Invalid or self-signed certificate')
        );
        $this->_addTranslation('Tb', __('Tb'));
        $this->_addTranslation('Gb', __('Gb'));
        $this->_addTranslation('Mb', __('Mb'));
        $this->_addTranslation('Kb', __('Kb'));
        $this->_addTranslation('b', __('b'));
    }
}
