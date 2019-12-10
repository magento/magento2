<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Block\Backend\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\Escaper;
use Magento\Framework\Mview\View;
use Magento\Framework\Phrase;

/**
 * Renderer for 'Schedule Status' column in indexer grid
 */
class ScheduleStatus extends AbstractRenderer
{
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Framework\Mview\ViewInterface
     */
    protected $viewModel;

     /**
      * @param \Magento\Backend\Block\Context $context
      * @param \Magento\Framework\Escaper $escaper
      * @param \Magento\Framework\Mview\ViewInterface $viewModel
      * @param array $data
      */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        Escaper $escaper,
        View $viewModel,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->escaper = $escaper;
        $this->viewModel = $viewModel;
    }

    /**
     * Render indexer status
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        try {
            if (!$row->getIsScheduled()) {
                return '';
            }

            try {
                $view = $this->viewModel->load($row->getIndexerId());
            } catch (\InvalidArgumentException $exception) {
                // No view for this index.
                return '';
            }

            $state = $view->getState()->loadByView($view->getId());
            $changelog = $view->getChangelog()->setViewId($view->getId());
            $currentVersionId = $changelog->getVersion();
            $count = count($changelog->getList($state->getVersionId(), $currentVersionId));

            if ($count > 1000) {
                $class = 'grid-severity-critical';
            } elseif ($count > 100) {
                $class = 'grid-severity-major';
            } elseif ($count > 10) {
                $class = 'grid-severity-minor';
            } else {
                $class = 'grid-severity-notice';
            }

            if ($state->getStatus() !== $state::STATUS_IDLE) {
                $class = 'grid-severity-minor';
            }

            $text = new Phrase(
                "%status (%count in backlog)",
                [
                    'status' => $state->getStatus(),
                    'count' => $count,
                ]
            );

            return '<span class="' . $class . '"><span>' . $text . '</span></span>';
        } catch (\Exception $exception) {
            return '<span class="grid-severity-minor"><span>' .
                $this->escaper->escapeHtml(
                    get_class($exception) . ': ' . $exception->getMessage()
                ) . '</span></span>';
        }
    }
}
