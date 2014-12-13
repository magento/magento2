<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Review\Test\Fixture;

use Mtf\Factory\Factory;
use Mtf\Fixture\DataFixture;

/**
 * Review fixture
 *
 */
class Review extends DataFixture
{
    /**
     * Get review title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData('fields/title/value');
    }

    /**
     * {inheritdoc}
     */
    protected function _initData()
    {
        $this->_data = [
            'fields' => [
                'nickname' => [
                    'value' => 'Guest customer %isolation%',
                ],
                'title' => [
                    'value' => 'Summary review %isolation%',
                ],
                'detail' => [
                    'value' => 'Text review %isolation%',
                ],
            ],
        ];

        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoReviewReview($this->_dataConfig, $this->_data);
    }
}
