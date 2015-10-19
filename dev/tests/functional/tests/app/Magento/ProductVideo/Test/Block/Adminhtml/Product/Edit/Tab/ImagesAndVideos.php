<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Block\Adminhtml\Product\Edit\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;

/**
 * Product images tab.
 */
class ImagesAndVideos extends Tab
{
    /**
     * Add video button CSS locator.
     *
     * @var string
     */
    protected $addVideoButton = '#product_info_tabs_image-management_content #add_video_button';

    /**
     * Video dialog CSS locator.
     *
     * @var string
     */
    protected $newVideoDialog = '.mage-new-video-dialog';

    /**
     * Image item CSS selector.
     *
     * @var string
     */
    protected $imageItem = '.image.item';

    /**
     * Gets video dialog.
     *
     * @return \Magento\ProductVideo\Test\Block\Adminhtml\Product\Edit\Tab\Images\VideoDialog
     */
    public function getVideoDialog()
    {
        $this->waitForElementVisible($this->newVideoDialog);
        return $this->blockFactory->create(
            'Magento\ProductVideo\Test\Block\Adminhtml\Product\Edit\Tab\Images\VideoDialog',
            ['element' => $this->browser->find($this->newVideoDialog)]
        );
    }

    /**
     * Clicks add video button.
     *
     * @return void
     */
    protected function clickAddVideo()
    {
        $this->_rootElement->find($this->addVideoButton)->click();
    }

    /**
     * Fill data to fields on tab.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fillFormTab(array $fields, SimpleElement $element = null)
    {
        if (!array_key_exists('images', $fields['media_gallery']['value'])) {
            return $this;
        }
        if ($fields['media_gallery']['value']['images'] == '') {
            $fields['media_gallery']['value']['images'] = [];
        }
        $currentImages = $this->getImageIds();
        $newImages = array_keys($fields['media_gallery']['value']['images']);
        $updateIds = array_intersect($currentImages, $newImages);
        $addIds = array_diff($newImages, $currentImages);
        $deleteIds = array_diff($currentImages, $newImages);

        foreach ($updateIds as $id) {
            $this->updateVideo($id, $fields['media_gallery']['value']['images'][$id]);
        }

        foreach ($deleteIds as $id) {
            $this->deleteVideo($id);
        }

        foreach ($addIds as $id) {
            $this->addVideo($fields['media_gallery']['value']['images'][$id]);
        }

        return $this;
    }

    /**
     * Adds new video.
     *
     * @param $data
     * @return void
     */
    protected function addVideo($data)
    {
        $this->clickAddVideo();
        $this->getVideoDialog()->fillForm($data)->clickSaveButton();
    }

    /**
     * Deletes video.
     *
     * @param $id
     * @return void
     */
    protected function deleteVideo($id)
    {
        $this->clickVideo($id);
        $this->getVideoDialog()->clickDeleteButton();
    }

    /**
     * Updates video.
     *
     * @param $id
     * @param $data
     * @return void
     */
    protected function updateVideo($id, $data)
    {
        $this->clickVideo($id);
        $this->getVideoDialog()->fillForm($data)->clickEditButton();
    }

    /**
     * Get data of tab.
     *
     * @param array|null $tabFields
     * @param SimpleElement|null $element
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDataFormTab($tabFields = null, SimpleElement $element = null)
    {
        $fields = reset($tabFields);
        $name = key($tabFields);
        $formData = [];
        if (empty($fields['value']) || !array_key_exists('images', $fields['value'])) {
            return '';
        }
        if ($fields['value']['images'] == '') {
            $fields['value']['images'] = [];
        }

        $formData[$name]['images'] = [];

        $imageArray = $fields['value']['images'];
        $resetImages = array_flip(array_keys($imageArray));

        foreach ($imageArray as $keyRoot => $fieldSet) {
            $image = $this->_rootElement->find($this->getImageSelector($resetImages[$keyRoot]));
            if ($image) {
                $image->click();
                $videoDialog = $this->getVideoDialog();
                $data = $videoDialog->getVideoInfo();
                foreach (array_keys($fieldSet) as $field) {
                    if (isset($data[$field])) {
                        $formData[$name]['images'][$keyRoot][$field] = $data[$field];
                    }
                }
                $videoDialog->clickCloseButton();
            }
        }

        if (count($formData[$name]['images']) == 0) {
            $formData[$name]['images'] = '';
        }

        return $formData;
    }

    /**
     * Gets image CSS selector.
     *
     * @param $id
     * @return string
     */
    protected function getImageSelector($id)
    {
        ++$id;
        return $this->imageItem . ':nth-child(' . $id . ') .draggable-handle';
    }

    /**
     * Returns emulated image index
     *
     * @return array
     */
    protected function getImageIds()
    {
        $images = $this->_rootElement->getElements($this->imageItem);
        return array_keys($images);
    }

    /**
     * Clicks on video image.
     *
     * @param $id
     * @return void
     */
    protected function clickVideo($id)
    {
        $this->_rootElement->find($this->getImageSelector($id))->click();
    }

    /**
     * Clicks on first video image.
     *
     * @return $this
     */
    public function clickFirstVideo()
    {
        $this->_rootElement->find($this->getImageSelector(0))->click();
        return $this;
    }
}
