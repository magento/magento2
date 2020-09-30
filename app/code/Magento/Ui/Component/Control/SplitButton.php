<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Component\Control;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Widget for standard button with a selection.
 *
 * @method string getTitle
 * @method string getLabel
 * @method string getButtonClass
 * @method string getId
 * @method string getClass
 * @method string getDataAttribute
 * @method string getStyle
 * @method string getDisabled
 * @method array getOptions
 * @method string getIdHard
 */
class SplitButton extends Button
{
    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    /**
     * @var Random
     */
    private $random;

    /**
     * @inheritDoc
     */
    public function __construct(
        Context $context,
        array $data = [],
        ?Random $random = null,
        ?SecureHtmlRenderer $htmlRenderer = null
    ) {
        $random = $random ?? ObjectManager::getInstance()->get(Random::class);
        $htmlRenderer = $htmlRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        parent::__construct($context, $data, $random, $htmlRenderer);
        $this->random = $random;
        $this->secureRenderer = $htmlRenderer;
    }

    /**
     * @inheritdoc
     */
    protected function getTemplatePath()
    {
        return 'Magento_Ui::control/button/split.phtml';
    }

    /**
     * Retrieve <div> wrapper attributes html
     *
     * @return string
     */
    public function getAttributesHtml()
    {
        $classes = [];

        if (!($title = $this->getTitle())) {
            $title = $this->getLabel();
        }

        if ($this->hasSplit()) {
            $classes[] = 'actions-split';
        }

        if ($this->getClass()) {
            $classes[] = $this->getClass();
        }

        return $this->attributesToHtml(['title' => $title, 'class' => join(' ', $classes)]);
    }

    /**
     * Get main button's "id" attribute value.
     *
     * @return string
     */
    private function getButtonId(): string
    {
        return $this->getId() .'-button';
    }

    /**
     * Retrieve button attributes html
     *
     * @return string
     */
    public function getButtonAttributesHtml()
    {
        $disabled = $this->getDisabled() ? 'disabled' : '';
        $classes = ['action-default', 'primary'];

        if (!($title = $this->getTitle())) {
            $title = $this->getLabel();
        }

        if ($this->getButtonClass()) {
            $classes[] = $this->getButtonClass();
        }

        if ($disabled) {
            $classes[] = $disabled;
        }

        $attributes = [
            'id' => $this->getButtonId(),
            'title' => $title,
            'class' => join(' ', $classes),
            'disabled' => $disabled,
        ];

        if ($idHard = $this->getIdHard()) {
            $attributes['id'] = $idHard;
        }

        //TODO perhaps we need to skip data-mage-init when disabled="disabled"
        if ($dataAttribute = $this->getDataAttribute()) {
            $this->getDataAttributes($dataAttribute, $attributes);
        }

        $html = $this->attributesToHtml($attributes);
        $html .= $this->getUiId();

        return $html;
    }

    /**
     * Retrieve toggle button attributes html
     *
     * @return string
     */
    public function getToggleAttributesHtml()
    {
        $disabled = $this->getDisabled() ? 'disabled' : '';
        $classes = ['action-toggle', 'primary'];

        if (!($title = $this->getTitle())) {
            $title = $this->getLabel();
        }

        if ($currentClass = $this->getClass()) {
            $classes[] = $currentClass;
        }

        if ($disabled) {
            $classes[] = $disabled;
        }

        $attributes = ['title' => $title, 'class' => join(' ', $classes), 'disabled' => $disabled];
        $this->getDataAttributes(['mage-init' => '{"dropdown": {}}', 'toggle' => 'dropdown'], $attributes);

        $html = $this->attributesToHtml($attributes);
        $html .= $this->getUiId('dropdown');

        return $html;
    }

    /**
     * Retrieve options attributes html
     *
     * @param string $key
     * @param array $option
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getOptionAttributesHtml($key, $option)
    {
        $disabled = !empty($option['disabled']) ? 'disabled' : '';
        $title = isset($option['title']) ? $option['title'] : $option['label'];
        $classes = ['item'];

        if (!empty($option['default'])) {
            $classes[] = 'item-default';
        }

        if ($disabled) {
            $classes[] = $disabled;
        }

        $attributes = $this->prepareOptionAttributes($option, $title, $classes, $disabled);
        $html = $this->attributesToHtml($attributes);
        $html .= $this->getUiId(isset($option['id']) ? $option['id'] : 'item' . '-' . $key);

        return $html;
    }

    /**
     * Retrieve "id" attribute value for an option.
     *
     * @param array $option
     * @return string
     */
    private function identifyOption(array $option): string
    {
        return isset($option['id'])
            ? $this->getId() .'-' .$option['id']
            : (isset($option['id_attribute']) ?
                $option['id_attribute']
                : $this->getId() .'-optId' .$this->random->getRandomString(10));
    }

    /**
     * Prepare option attributes
     *
     * @param array $option
     * @param string $title
     * @param string $classes
     * @param string $disabled
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function prepareOptionAttributes($option, $title, $classes, $disabled)
    {
        $attributes = [
            'id' => $this->identifyOption($option),
            'title' => $title,
            'class' => join(' ', $classes),
            'disabled' => $disabled,
        ];

        if (!empty($option['id_hard'])) {
            $attributes['id'] = $option['id_hard'];
        }

        if (isset($option['data_attribute'])) {
            $this->getDataAttributes($option['data_attribute'], $attributes);
        }

        return $attributes;
    }

    /**
     * Checks if the button needs actions-split functionality
     *
     * If this function returns false then split button will be rendered as simple button
     *
     * @return bool
     */
    public function hasSplit()
    {
        return $this->hasData('has_split') ? (bool)$this->getData('has_split') : true;
    }
    /**
     * Add data attributes to $attributes array
     *
     * @param array $data
     * @param array $attributes
     * @return void
     */
    protected function getDataAttributes($data, &$attributes)
    {
        foreach ($data as $key => $attr) {
            $attributes['data-' . $key] = is_scalar($attr) ? $attr : json_encode($attr);
        }
    }

    /**
     * @inheritDoc
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        /** @var array|null $options */
        $options = $this->getOptions() ?? [];
        foreach ($options as &$option) {
            $option['id_attribute'] = $this->identifyOption($option);
        }
        $this->setOptions($options);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAfterHtml(): ?string
    {
        $afterHtml = parent::getAfterHtml();

        /** @var array|null $options */
        $options = $this->getOptions() ?? [];
        foreach ($options as $option) {
            $id = $this->identifyOption($option);
            if (!empty($option['onclick'])) {
                $afterHtml .= $this->secureRenderer->renderEventListenerAsTag('onclick', $option['onclick'], "#$id");
            }
            if (!empty($option['style'])) {
                $afterHtml .= $this->secureRenderer->renderStyleAsTag($option['style'], "#$id");
            }
        }

        return $afterHtml;
    }
}
