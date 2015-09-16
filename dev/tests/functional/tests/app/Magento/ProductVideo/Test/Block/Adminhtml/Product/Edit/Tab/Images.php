<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Block\Adminhtml\Product\Edit\Tab;

use Magento\Backend\Test\Block\Widget\Tab;


/**
 * Class Images
 * Product images tab
 */
class Images extends Tab
{
    /**
     * Add video button CSS locator
     * @var string
     */
    protected $addVideoButton = '#product_info_tabs_image-management_content #add_video_button';

    /**
     * Video dialog CSS locator
     * @var string
     */
    protected $newVideoDialog = '.mage-new-video-dialog';

    /**
     * First video button CSS locator
     * @var string
     */
    //protected $firstVideoButton = '#media_gallery_content img.product-image.video-item';
    protected $firstVideoButton = '.image.item.base-image.video-item';

    /**
     * Gets add video button element
     * @return \Magento\Mtf\Client\ElementInterface
     */
    public function getAddVideoButton()
    {
        return $this->_rootElement->find($this->addVideoButton);
    }

    /**
     * Gets first video button element
     * @return \Magento\Mtf\Client\ElementInterface
     */
    public function getFirstVideoButton()
    {
        return $this->_rootElement->find($this->firstVideoButton);
    }

    /**
     * Clicks add video button
     */
    public function clickAddVideo()
    {
        $this->getAddVideoButton()->click();
    }

    /**
     * Clicks first video
     */
    public function clickFirstVideo()
    {
        $this->getFirstVideoButton()->click();
    }


    /**
     * Gets video dialog
     *
     * @return \Magento\Mtf\Block\BlockInterface
     */
    public function getVideoDialog()
    {
        $this->waitForElementVisible($this->newVideoDialog);
        return $this->blockFactory->create(
            'Magento\ProductVideo\Test\Block\Adminhtml\Product\Edit\Tab\Images\videoDialog',
            ['element' => $this->browser->find($this->newVideoDialog)]
        );
    }
}