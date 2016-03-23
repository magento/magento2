<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Block\System\Config\System\Storage\Media;

/**
 * Synchronize button renderer
 */
class Synchronize extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var string
     */
    protected $_template = 'Magento_MediaStorage::system/config/system/storage/media/synchronize.phtml';

    /**
     * @var \Magento\MediaStorage\Model\File\Storage
     */
    protected $_fileStorage;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\MediaStorage\Model\File\Storage $fileStorage
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\MediaStorage\Model\File\Storage $fileStorage,
        array $data = []
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
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
            [
                'id' => 'synchronize_button',
                'label' => __('Synchronize'),
            ]
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

        if ($flag->getState() == \Magento\MediaStorage\Model\File\Storage\Flag::STATE_NOTIFIED && is_array(
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
            $storageType = \Magento\MediaStorage\Model\File\Storage::STORAGE_MEDIA_FILE_SYSTEM;
            $connectionName = '';
        }

        return ['storage_type' => $storageType, 'connection_name' => $connectionName];
    }
}
