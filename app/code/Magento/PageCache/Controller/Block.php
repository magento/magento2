<?php
/**
 * PageCache controller
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Controller;

abstract class Block extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $translateInline;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Translate\InlineInterface $translateInline
    ) {
        parent::__construct($context);
        $this->translateInline = $translateInline;
    }

    /**
     * Get blocks from layout by handles
     *
     * @return array [\Element\BlockInterface]
     */
    protected function _getBlocks()
    {
        $blocks = $this->getRequest()->getParam('blocks', '');
        $handles = $this->getRequest()->getParam('handles', '');

        if (!$handles || !$blocks) {
            return [];
        }
        $blocks = json_decode($blocks);
        $handles = json_decode($handles);

        $this->_view->loadLayout($handles, true, true, false);
        $data = [];

        $layout = $this->_view->getLayout();
        foreach ($blocks as $blockName) {
            $blockInstance = $layout->getBlock($blockName);
            if (is_object($blockInstance)) {
                $data[$blockName] = $blockInstance;
            }
        }

        return $data;
    }
}
