<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Translation\Model;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Translate\Inline;
use Magento\Framework\App\Area;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Translation\Model\Inline\Parser;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for \Magento\Translation\Model\Inline\Parser.
 */
class InlineParserTest extends TestCase
{
    private const STUB_STORE = 'default';
    private const XML_PATH_TRANSLATE_INLINE_ACTIVE = 'dev/translate_inline/active';

    /**
     * @var Parser
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $inline = Bootstrap::getObjectManager()->create(Inline::class);
        $this->model = Bootstrap::getObjectManager()->create(Parser::class, ['translateInline' => $inline]);
        Bootstrap::getObjectManager()->get(MutableScopeConfigInterface::class)
            ->setValue(self::XML_PATH_TRANSLATE_INLINE_ACTIVE, true, ScopeInterface::SCOPE_STORE, self::STUB_STORE);
    }

    /**
     * Process ajax post test
     *
     * @dataProvider processAjaxPostDataProvider
     *
     * @param string $originalText
     * @param string $translatedText
     * @param string $area
     * @param bool|null $isPerStore
     * @return void
     */
    public function testProcessAjaxPost(
        string $originalText,
        string $translatedText,
        string $area,
        ?bool $isPerStore = null
    ): void {
        Bootstrap::getObjectManager()->get(State::class)
            ->setAreaCode($area);

        $inputArray = [['original' => $originalText, 'custom' => $translatedText]];
        if ($isPerStore !== null) {
            $inputArray[0]['perstore'] = $isPerStore;
        }
        $this->model->processAjaxPost($inputArray);

        $model = Bootstrap::getObjectManager()->create(StringUtils::class);
        $model->load($originalText);

        try {
            $this->assertEquals($translatedText, $model->getTranslate());
            $model->delete();
        } catch (\Exception $e) {
            $model->delete();
            Bootstrap::getObjectManager()->get(LoggerInterface::class)
                ->critical($e);
        }
    }

    /**
     * Data provider for testProcessAjaxPost
     *
     * @return array
     */
    public static function processAjaxPostDataProvider(): array
    {
        return [
            ['original text 1', 'translated text 1', Area::AREA_ADMINHTML],
            ['original text 1', 'translated text 1', Area::AREA_FRONTEND],
            ['original text 2', 'translated text 2', Area::AREA_ADMINHTML, true],
            ['original text 2', 'translated text 2', Area::AREA_FRONTEND, true],
        ];
    }

    /**
     * Set get is json test
     *
     * @dataProvider allowedAreasDataProvider
     *
     * @param string $area
     * @return void
     */
    public function testSetGetIsJson(string $area): void
    {
        Bootstrap::getObjectManager()->get(State::class)
            ->setAreaCode($area);

        $isJsonProperty = new \ReflectionProperty(get_class($this->model), '_isJson');
        $isJsonProperty->setAccessible(true);

        $this->assertFalse($isJsonProperty->getValue($this->model));

        $setIsJsonMethod = new \ReflectionMethod($this->model, 'setIsJson');
        $setIsJsonMethod->setAccessible(true);
        $setIsJsonMethod->invoke($this->model, true);

        $this->assertTrue($isJsonProperty->getValue($this->model));
    }

    /**
     * Data provider for testSetGetIsJson
     *
     * @return array
     */
    public static function allowedAreasDataProvider(): array
    {
        return [
            [Area::AREA_ADMINHTML],
            [Area::AREA_FRONTEND]
        ];
    }
}
