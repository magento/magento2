<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Block\Adminhtml\Product\Edit\Tab\Images;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Product new video dialog.
 */
class VideoDialog extends Form
{
    /**
     * Save button CSS selector.
     *
     * @var string
     */
    protected $saveButton = '.video-create-button';

    /**
     * Save button CSS selector.
     *
     * @var string
     */
    protected $editButton = '.video-edit';

    /**
     * Delete button CSS selector.
     *
     * @var string
     */
    protected $deleteButton = '.video-delete-button';

    /**
     * Get video information button CSS selector.
     *
     * @var string
     */
    protected $getVideoButton = '#new_video_get';

    /**
     * Screenshot preview image CSS selector.
     *
     * @var string
     */
    protected $screenshotPreview = '#new_video_screenshot_preview + img';

    /**
     * Video Player CSS Selector
     *
     * @var string
     */
    private $videoPlayer = '#new-video div.video-player-container';

    /**
     * Close button CSS selector.
     *
     * @var string
     */
    protected $closeButton = '.action-close';

    /**
     * Clicks 'Save' button.
     *
     * @return $this
     */
    public function clickSaveButton()
    {
        $this->_rootElement->find($this->saveButton)->click();
        return $this;
    }

    /**
     * Clicks 'Edit' button.
     *
     * @return $this
     */
    public function clickEditButton()
    {
        $this->waitForElementNotDisabled($this->editButton);
        $this->_rootElement->find($this->editButton)->click();
        return $this;
    }

    /**
     * Clicks 'Delete' button.
     *
     * @return $this
     */
    public function clickDeleteButton()
    {
        $this->_rootElement->find($this->deleteButton)->click();
        return $this;
    }

    /**
     * Clicks 'Close' button.
     *
     * @return $this
     */
    public function clickCloseButton()
    {
        $this->_rootElement->find($this->closeButton)->click();
        return $this;
    }

    /**
     * Fills form with data
     *
     * @param array $data
     * @return $this
     * @throws \Exception
     */
    public function fillForm(array $data)
    {
        $data = $this->dataMapping($data);
        if (isset($data['video_url'])) {
            $videoFill = ['video_url' => $data['video_url']];
            unset($data['video_url']);
            $this->_fill($videoFill);
            $this->_rootElement->find($this->getVideoButton)->click();
            $this->waitForElementVisible($this->screenshotPreview);
            $this->waitForElementVisible($this->videoPlayer);
        }
        $this->_fill($data);
        return $this;
    }

    /**
     * Wait for element is not disabled in the block
     *
     * @param string $selector
     * @param string $strategy
     * @return bool|null
     */
    public function waitForElementNotDisabled($selector, $strategy = Locator::SELECTOR_CSS)
    {
        $browser = $this->browser;
        return $browser->waitUntil(
            function () use ($browser, $selector, $strategy) {
                $element = $browser->find($selector, $strategy);
                return $element->isDisabled() == false ? true : null;
            }
        );
    }

    /**
     * Gets video info
     *
     * @return array
     */
    public function getVideoInfo()
    {
        $data = [];
        foreach (array_keys($this->mapping) as $field) {
            $data[$field] = $this->_rootElement->find($field, Locator::SELECTOR_NAME)->getValue();
        }
        return $data;
    }

    /**
     * Validates data in form.
     *
     * @param array $video
     * @return bool
     */
    public function validate(array $video)
    {
        $result = true;
        $data = $this->getVideoInfo();
        foreach ($video as $key => $value) {
            if ($value != $data[$key]) {
                $result = false;
                break;
            }
        }
        return $result;
    }
}
