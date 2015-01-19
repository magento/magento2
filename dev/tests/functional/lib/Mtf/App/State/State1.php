<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mtf\App\State;

use Mtf\ObjectManager;
use Mtf\Fixture\FixtureFactory;
use Magento\Core\Test\Fixture\ConfigData;

/**
 * Class State1
 * Example Application State class
 */
class State1 extends AbstractState
{
    // TODO: Move data set to ConfigData fixture after implement merging fixture xml
    /**
     * Data set for configuration state
     *
     * @var array
     */
    protected $configDataSet = [
        'section' => [
            [
                'path' => 'cms/wysiwyg/enabled',
                'scope' => 'default',
                'scope_id' => 1,
                'value' => 'disabled',
            ],
        ]
    ];

    /**
     * Configuration fixture
     *
     * @var ConfigData
     */
    protected $config;

    /**
     * @construct
     * @param FixtureFactory $fixtureFactory
     */
    public function __construct(FixtureFactory $fixtureFactory)
    {
        $this->config = $fixtureFactory->createByCode('configData', ['data' => $this->configDataSet]);
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        parent::apply();
        if (file_exists(dirname(dirname(dirname(MTF_BP))) . '/app/etc/config.php')) {
            $this->config->persist();
        }
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Configuration Profile #1';
    }
}
