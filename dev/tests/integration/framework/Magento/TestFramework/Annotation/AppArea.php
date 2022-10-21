<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Annotation;

use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Application;
use Magento\TestFramework\Fixture\ParserInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class AppArea
{
    public const ANNOTATION_NAME = 'magentoAppArea';

    /**
     * @var Application
     */
    private $_application;

    /**
     * List of allowed areas.
     *
     * @var array
     */
    private $_allowedAreas = [
        \Magento\Framework\App\Area::AREA_GLOBAL,
        \Magento\Framework\App\Area::AREA_ADMINHTML,
        \Magento\Framework\App\Area::AREA_FRONTEND,
        \Magento\Framework\App\Area::AREA_WEBAPI_REST,
        \Magento\Framework\App\Area::AREA_WEBAPI_SOAP,
        \Magento\Framework\App\Area::AREA_CRONTAB,
        \Magento\Framework\App\Area::AREA_GRAPHQL
    ];

    /**
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->_application = $application;
    }

    /**
     * Get current application area
     *
     * @param array $annotations
     * @return string
     * @throws LocalizedException
     */
    protected function _getTestAppArea($annotations)
    {
        $area = isset(
            $annotations['method'][self::ANNOTATION_NAME]
        ) ? current(
            $annotations['method'][self::ANNOTATION_NAME]
        ) : (isset(
            $annotations['class'][self::ANNOTATION_NAME]
        ) ? current(
            $annotations['class'][self::ANNOTATION_NAME]
        ) : \Magento\TestFramework\Application::DEFAULT_APP_AREA);

        if (false == in_array($area, $this->_allowedAreas)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Invalid "@magentoAppArea" annotation, can be "%1" only.',
                    implode('", "', $this->_allowedAreas)
                )
            );
        }

        return $area;
    }

    /**
     * Start test case event observer
     *
     * @param TestCase $test
     * @throws LocalizedException
     */
    public function startTest(TestCase $test)
    {
        $values = [];
        try {
            $values = $this->parse($test);
        } catch (\Throwable $exception) {
            ExceptionHandler::handle(
                'Unable to parse fixtures',
                get_class($test),
                $test->getName(false),
                $exception
            );
        }

        $area = $values[0]['area'] ?? Application::DEFAULT_APP_AREA;

        if ($this->_application->getArea() !== $area) {
            $this->_application->reinitialize();

            if ($this->_application->getArea() !== $area) {
                $this->_application->loadArea($area);
            }
        }
    }

    /**
     * Returns AppArea fixtures configuration
     *
     * @param TestCase $test
     * @return array
     * @throws LocalizedException
     */
    private function parse(TestCase $test): array
    {
        $objectManager = Bootstrap::getObjectManager();
        $parsers = $objectManager
            ->create(
                \Magento\TestFramework\Annotation\Parser\Composite::class,
                [
                    'parsers' => [
                        $objectManager->get(\Magento\TestFramework\Annotation\Parser\AppArea::class),
                        $objectManager->get(\Magento\TestFramework\Fixture\Parser\AppArea::class)
                    ]
                ]
            );
        $values = $parsers->parse($test, ParserInterface::SCOPE_METHOD)
            ?: $parsers->parse($test, ParserInterface::SCOPE_CLASS);

        if (count($values) > 1) {
            throw new LocalizedException(
                __('Only one "@magentoAppArea" annotation is allowed per test')
            );
        }
        return $values;
    }
}
