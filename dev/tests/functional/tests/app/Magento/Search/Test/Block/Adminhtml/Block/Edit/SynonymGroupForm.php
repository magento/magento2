<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\Block\Adminhtml\Block\Edit;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Form for Synonym Group creation.
 */
class SynonymGroupForm extends Form
{
    /**
     * Content Editor toggle button id.
     *
     * @var string
     */
    protected $toggleButton = "#toggleblock_content";

    /**
     * Synonym Group Content area.
     *
     * @var string
     */
    protected $contentForm = '[name="content"]';

    /**
     * Fill the page form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        return parent::fill($fixture, $element);
    }
}
