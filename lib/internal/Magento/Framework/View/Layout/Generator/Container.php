<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Generator;

use Magento\Framework\View\Layout;

/**
 * Layout Container Class
 */
class Container implements Layout\GeneratorInterface
{
    /**#@+
     * Names of container options in layout
     */
    const CONTAINER_OPT_HTML_TAG = 'htmlTag';
    const CONTAINER_OPT_HTML_CLASS = 'htmlClass';
    const CONTAINER_OPT_HTML_ID = 'htmlId';
    const CONTAINER_OPT_LABEL = 'label';
    /**#@-*/

    const TYPE = 'container';

    /**
     * @var array
     */
    protected $allowedTags = [
        'aside',
        'dd',
        'div',
        'dl',
        'fieldset',
        'main',
        'nav',
        'header',
        'footer',
        'ol',
        'p',
        'section',
        'table',
        'tfoot',
        'ul',
        'article',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
    ];

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * Process container elements
     *
     * @param \Magento\Framework\View\Layout\Reader\Context $readerContext
     * @param Context $generatorContext
     * @return $this
     */
    public function process(Layout\Reader\Context $readerContext, Layout\Generator\Context $generatorContext)
    {
        $structure = $generatorContext->getStructure();
        $scheduledStructure = $readerContext->getScheduledStructure();
        foreach ($scheduledStructure->getElements() as $elementName => $element) {
            list($type, $data) = $element;
            if ($type === self::TYPE) {
                $this->generateContainer($structure, $elementName, $data['attributes']);
                $scheduledStructure->unsetElement($elementName);
            }
        }
        return $this;
    }

    /**
     * Set container-specific data to structure element
     *
     * @param \Magento\Framework\View\Layout\Data\Structure $structure
     * @param string $elementName
     * @param array $options
     * @return void
     */
    public function generateContainer(
        Layout\Data\Structure $structure,
        $elementName,
        $options
    ) {
        unset($options['type']);

        $this->validateOptions($options);

        foreach ($options as $key => $value) {
            $structure->setAttribute($elementName, $key, $value);
        }
    }

    /**
     * Validate container options
     *
     * @param array $options
     *
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validateOptions($options)
    {
        if (!empty($options[Layout\Element::CONTAINER_OPT_HTML_TAG])
            && !in_array(
                $options[Layout\Element::CONTAINER_OPT_HTML_TAG],
                $this->allowedTags
            )
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase(
                    'Html tag "%1" is forbidden for usage in containers. Consider to use one of the allowed: %2.',
                    [$options[Layout\Element::CONTAINER_OPT_HTML_TAG], implode(', ', $this->allowedTags)]
                )
            );
        }

        if (empty($options[Layout\Element::CONTAINER_OPT_HTML_TAG])
            && (
                !empty($options[Layout\Element::CONTAINER_OPT_HTML_ID])
                || !empty($options[Layout\Element::CONTAINER_OPT_HTML_CLASS])
            )
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('HTML ID or class will not have effect, if HTML tag is not specified.')
            );
        }
    }
}
