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
namespace Magento\Backend\Block\System\Config\System\Storage\Media;

/**
 * Synchronize button renderer
 */
class Synchronize extends \Magento\Backend\Block\System\Config\Form\Field
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::system/config/system/storage/media/synchronize.phtml';

    /**
     * @var \Magento\Core\Model\File\Storage
     */
    protected $_fileStorage;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\File\Storage $fileStorage
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\File\Storage $fileStorage,
        array $data = array()
    ) {
        $this->_fileStorage = $fileStorage;
        parent::__construct($context, $data);
    }

    /**
     * Remove scope label
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for synchronize button
     *
     * @return string
     */
    public function getAjaxSyncUrl()
    {
        return $this->getUrl('*/system_config_system_storage/synchronize');
    }

    /**
     * Return ajax url for synchronize button
     *
     * @return string
     */
    public function getAjaxStatusUpdateUrl()
    {
        return $this->getUrl('*/system_config_system_storage/status');
    }

    /**
     * Generate synchronize button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            array(
                'id' => 'synchronize_button',
                'label' => __('Synchronize'),
                'onclick' => 'javascript:synchronize(); return false;'
            )
        );

        return $button->toHtml();
    }

    /**
     * Retrieve last sync params settings
     *
     * Return array format:
     * array (
     *  => storage_type     int,
     *  => connection_name  string
     * )
     *
     * @return array
     */
    public function getSyncStorageParams()
    {
        $flag = $this->_fileStorage->getSyncFlag();
        $flagData = $flag->getFlagData();

        if ($flag->getState() == \Magento\Core\Model\File\Storage\Flag::STATE_NOTIFIED && is_array(
            $flagData
        ) && isset(
            $flagData['destination_storage_type']
        ) && $flagData['destination_storage_type'] != '' && isset(
            $flagData['destination_connection_name']
        )
        ) {
            $storageType = $flagData['destination_storage_type'];
            $connectionName = $flagData['destination_connection_name'];
        } else {
            $storageType = \Magento\Core\Model\File\Storage::STORAGE_MEDIA_FILE_SYSTEM;
            $connectionName = '';
        }

        return array('storage_type' => $storageType, 'connection_name' => $connectionName);
    }
}
