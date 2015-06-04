<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model;

use Magento\Framework\View\LayoutInterface;
use Magento\UrlRewrite\Model\Mode\ModeInterface;

class Modes
{
    /**
     * @var ModeInterface[]
     */
    protected $modes = [];
    protected $options;
    protected $sortField = 'sort_order';
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * constructor
     *
     * @param LayoutInterface $layout
     * @param array $modes
     */
    public function __construct(
        LayoutInterface $layout,
        array $modes = []
    )
    {
        $this->layout = $layout;
        $this->modes = $modes;
        uasort($this->modes, [$this, 'cmp']);
    }

    /**
     * get the current modes
     *
     * @return ModeInterface[]
     */
    public function getModes()
    {
        return $this->modes;
    }

    /**
     * get the list of modes as options for select elements
     *
     * @return array
     */
    public function toOptionsArray()
    {
        if (is_null($this->options)) {
            $this->options = [];
            foreach ($this->getModes() as $id => $mode) {
                $this->options[$id] = $mode->getLabel();
            }
        }
        return $this->options;
    }

    /**
     * comparison method used for sorting the modes
     *
     * @param $elementA
     * @param $elementB
     * @return int
     */
    protected function cmp(ModeInterface $elementA, ModeInterface $elementB)
    {
        $sortIndexA = intval($elementA->getSortOrder());
        $sortIndexB = intval($elementB->getSortOrder());
        if ($sortIndexA == $sortIndexB) {
            return 0;
        }
        return $sortIndexA < $sortIndexB ? -1 : 1;
    }

    /**
     * get the block instance used for the admin form
     *
     * @param $mode
     * @param array $data
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    public function getBlockInstance($mode, $data = [])
    {
        $modeInstance = $this->modes[$mode];
        $block = $this->layout->createBlock($modeInstance->getEditBlockClass(), '', $data);
        return $block;
    }

    public function getModeByUrlRewrite(\Magento\UrlRewrite\Model\UrlRewrite $urlRewrite)
    {
        $modes = $this->getModes();
        //start from the bottom
        $modes = array_reverse($modes, true);
        foreach ($modes as $key => $modeInstance) {
            /** @var ModeInterface $modeInstance */
            if ($modeInstance->match($urlRewrite)) {
                return $key;
            }
        }
        return null;
    }
}