<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Block\Adminhtml\Product\Edit\Tab\Images;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;


/**
 * Class NewVideoDialog
 * Product new video dialog
 */
class videoDialog extends Block
{
    /**
     * Video url field name
     *
     * @var string
     */
    protected $videoUrlFieldName = 'video_url';

    /**
     * Save button css selector
     * @var string
     */
    protected $addButton = '.video-create-button';

    /**
     * Save button css selector
     * @var string
     */
    protected $editButton = '.video-edit';

    /**
     * Delete button css selector
     * @var string
     */
    protected $deleteButton = '.video-delete-button';

    /**
     * Get video info button css selector
     * @var string
     */
    protected $getVideoInfoButton = '#new_video_get';

    /**
     * Preview image css selector
     * @var string
     */
    protected $preview = '#new_video_screenshot_preview + img';

    /**
     * Gets add button
     *
     * @return \Magento\Mtf\Client\ElementInterface
     */
    protected function getGetVideoInfoButton()
    {
        return $this->_rootElement->find($this->getVideoInfoButton);
    }

    /**
     * Gets add button
     *
     * @return \Magento\Mtf\Client\ElementInterface
     */
    protected function getAddButton()
    {
        return $this->_rootElement->find($this->addButton);
    }

    /**
     * Gets edit button
     *
     * @return \Magento\Mtf\Client\ElementInterface
     */
    protected function getEditButton()
    {
        return $this->_rootElement->find($this->editButton);
    }

    /**
     * Gets delete button
     *
     * @return \Magento\Mtf\Client\ElementInterface
     */
    protected function getDeleteButton()
    {
        return $this->_rootElement->find($this->deleteButton);
    }

    /**
     * Clicks add button
     */
    public function add()
    {
        $this->getAddButton()->click();
    }

    /**
     * Gets video info
     */
    public function getVideoInfo()
    {
        $this->getGetVideoInfoButton()->click();
        $rootElement = $this->_rootElement;
        $previewSelector = $this->preview;
        $this->_rootElement->waitUntil(
            function () use ($rootElement, $previewSelector) {
                return $rootElement->find($previewSelector)->isVisible() ? true : null;
            }
        );
    }

    /**
     * Clicks edit button
     */
    public function edit()
    {
        $this->getEditButton()->click();
    }

    /**
     * Clicks edit button
     */
    public function delete()
    {
        $this->getDeleteButton()->click();
    }

    /**
     * Fill the form
     * @param array $data
     */
    public function fill(array $data)
    {
        foreach ($data as $key => $value) {
            $this->_rootElement->find($key, Locator::SELECTOR_NAME)->setValue($value);
        }
    }

    /**
     * Validates form data
     *
     * @param array $data
     * @return bool
     */
    public function validate(array $data)
    {
        foreach ($data as $key => $value) {
            $formValue = $this->_rootElement->find($key, Locator::SELECTOR_NAME)->getValue();
            if ($value != $formValue) {
                return false;
            }
        }
        return true;
    }

    /**
     * Fill the video field in form
     * @param string $data
     */
    public function fillVideoUrl($data)
    {
        $this->_rootElement->find($this->videoUrlFieldName, Locator::SELECTOR_NAME)->setValue($data);
    }

}