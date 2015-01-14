<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Code;

/**
 * Block that renders CSS tab
 */
class Css extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\DesignEditor\Helper\Data $urlEncoder,
        array $data = []
    ) {
        $this->urlEncoder = $urlEncoder;
        parent::__construct($context, $data);
    }

    /**
     * Get CSS file assets
     *
     * Note: someone must set them in the first place
     *
     * @return \Magento\Framework\View\Asset\LocalInterface[]
     */
    public function getAssets()
    {
        return $this->_getData('assets');
    }

    /**
     * Get url to download CSS file
     *
     * @param string $fileId
     * @param int $themeId
     * @return string
     */
    public function getDownloadUrl($fileId, $themeId)
    {
        return $this->getUrl(
            'adminhtml/system_design_theme/downloadCss',
            ['theme_id' => $themeId, 'file' => $this->urlEncoder->encode($fileId)]
        );
    }

    /**
     * Check if files group needs "add" button
     *
     * @return false
     */
    public function hasAddButton()
    {
        return false;
    }

    /**
     * Check if files group needs download buttons next to each file
     *
     * @return true
     */
    public function hasDownloadButton()
    {
        return true;
    }
}
