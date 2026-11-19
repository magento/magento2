<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Element\Html;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Date element block
 */
class Date extends \Magento\Framework\View\Element\Template
{
    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    /**
     * @param Context $context
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        parent::__construct($context, $data);
        $this->secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
    }

    /**
     * Render block HTML
     *
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _toHtml()
    {
        $html = '<input type="text" name="' . $this->getName() . '" id="' . $this->getId() . '" ';
        $html .= 'value="' . $this->escapeHtml($this->getValue()) . '" ';
        $html .= 'class="' . $this->getClass() . '" ' . $this->getExtraParams() . '/> ';
        $calendarYearsRange = $this->getYearsRange();
        $changeMonth = $this->getChangeMonth();
        $changeYear = $this->getChangeYear();
        $maxDate = $this->getMaxDate();
        $showOn = $this->getShowOn();
        $firstDay = $this->getFirstDay();

        $script = '
            require(["jquery", "mage/calendar"], function($){
                    $("#' .
            $this->getId() .
            '").calendar({
                        showsTime: ' .
            ($this->getTimeFormat() ? 'true' : 'false') .
            ',
                        ' .
            ($this->getTimeFormat() ? 'timeFormat: "' .
            $this->getTimeFormat() .
            '",' : '') .
            '
                        dateFormat: "' .
            $this->getDateFormat() .
            '",
                        buttonImage: "' .
            $this->getImage() .
            '",
                        ' .
            ($calendarYearsRange ? 'yearRange: "' .
            $calendarYearsRange .
            '",' : '') .
            '
                        buttonText: "' .
            (string)new \Magento\Framework\Phrase(
                'Select Date'
            ) .
            '"' . ($maxDate ? ', maxDate: "' . $maxDate . '"' : '') .
            ($changeMonth === null ? '' : ', changeMonth: ' . $changeMonth) .
            ($changeYear === null ? '' : ', changeYear: ' . $changeYear) .
            ($showOn ? ', showOn: "' . $showOn . '"' : '') .
            ($firstDay ? ', firstDay: ' . $firstDay : '') .
            '})
            });
            ';
        $html .= $this->secureRenderer->renderTag('script', ['type' => 'text/javascript'], $script, false);

        return $html;
    }

    /**
     * Convert special characters to HTML entities
     *
     * @return string
     */
    public function getEscapedValue()
    {
        if ($this->getFormat() && $this->getValue()) {
            // phpcs:ignore PHPCompatibility.FunctionUse.RemovedFunctions.strftimeDeprecated
            return strftime($this->getFormat(), strtotime($this->getValue()));
        }
        return $this->escapeHtml($this->getValue());
    }

    /**
     * Produce and return block's html output
     *
     * {@inheritdoc}
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->toHtml();
    }
}
