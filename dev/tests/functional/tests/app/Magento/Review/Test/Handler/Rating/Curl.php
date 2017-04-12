<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Handler\Rating;

use Magento\Backend\Test\Handler\Extractor;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Curl handler for creating product Rating through backend.
 */
class Curl extends AbstractCurl implements RatingInterface
{
    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'is_active' => [
            'Yes' => 1,
            'No' => 0,
        ],
    ];

    /**
     * Mapping stores value
     *
     * @var array
     */
    protected $mappingStores = [
        'Main Website/Main Website Store/Default Store View' => 1,
    ];

    /**
     * Post request for creating product Rating in backend
     *
     * @param FixtureInterface|null $rating [optional]
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $rating = null)
    {
        $url = $_ENV['app_backend_url'] . 'review/rating/save/';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $data = $this->replaceMappingData($this->prepareData($rating->getData()));

        $data['stores'] = is_array($data['stores']) ? $data['stores'] : [$data['stores']];
        $data += $this->getAdditionalData();
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();
        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception(
                'Product Rating entity creating by curl handler was not successful! Response:' . $response
            );
        }

        $ratingId = $this->getProductRatingId();
        return [
            'rating_id' => $ratingId,
            'options' => $this->getRatingOptions($ratingId)
        ];
    }

    /**
     * Prepare POST data for creating rating request
     *
     * @param array $data
     * @return array
     */
    protected function prepareData(array $data)
    {
        if (isset($data['stores'])) {
            foreach ($data['stores'] as $key => $store) {
                if (isset($this->mappingStores[$store])) {
                    $data['stores'][$key] = $this->mappingStores[$store];
                }
            }
        }

        return $data;
    }

    /**
     * Get product Rating id
     *
     * @return mixed
     */
    protected function getProductRatingId()
    {
        $url = 'review/rating/index/sort/rating_id/dir/desc/';
        $regex = '/data-column="rating_id"[^>]*>\s*([0-9]+)\s*</';
        $extractor = new Extractor($url, $regex);
        $match = $extractor->getData();

        return empty($match[1]) ? null : $match[1];
    }

    /**
     * Get rating options
     *
     * @param int $ratingId
     * @return array
     */
    protected function getRatingOptions($ratingId)
    {
        $url = 'review/rating/edit/id/' . $ratingId;
        $regex = '/<input[^>]+name="option_title\[(\d+)\]"[^>]+>/';
        $extractor = new Extractor($url, $regex, true);
        $matches = $extractor->getData();

        if (empty($matches[1])) {
            return [];
        }
        array_unshift($matches[1], null);
        return array_filter($matches[1]);
    }

    /**
     * Return additional data for curl request
     *
     * @return array
     */
    protected function getAdditionalData()
    {
        return [
            'rating_codes' => [1 => ''],
            'option_title' => [
                'add_1' => 1,
                'add_2' => 2,
                'add_3' => 3,
                'add_4' => 4,
                'add_5' => 5,
            ],
        ];
    }
}
