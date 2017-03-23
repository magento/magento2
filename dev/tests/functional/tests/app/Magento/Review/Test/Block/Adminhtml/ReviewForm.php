<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Block\Adminhtml;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Review edit form.
 */
class ReviewForm extends Form
{
    /**
     * Posted by field.
     *
     * @var string
     */
    protected $customer = '#customer';

    /**
     * Rating status.
     *
     * @var string
     */
    protected $status = '[name=status_id]';

    /**
     * 'Save Review' button.
     *
     * @var string
     */
    protected $saveButton = '[data-ui-id$=save-button-button]';

    /**
     * Fill the review form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $data = $fixture->getData();
        if (isset($data['entity_id'])) {
            unset($data['entity_id']);
        }
        $mapping = $this->dataMapping($data);
        $this->_fill($mapping, $element);

        return $this;
    }

    /**
     * Get data from 'Posted By' field.
     *
     * @return string
     */
    public function getPostedBy()
    {
        return $this->_rootElement->find($this->customer, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Get data from Status field.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->_rootElement->find($this->status, Locator::SELECTOR_CSS, 'select')->getText();
    }

    /**
     * Set approve review.
     *
     * @return void
     */
    public function setApproveReview()
    {
        $this->_rootElement->find($this->status, Locator::SELECTOR_CSS, 'select')->setValue('Approved');
    }
}
