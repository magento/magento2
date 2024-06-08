<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Block\Widget\Grid\Column\Filter;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Column\Filter\Datetime;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Escaper;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\Repository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class DateTimeTest to test Magento\Backend\Block\Widget\Grid\Column\Filter\Date
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DatetimeTest extends TestCase
{
    /** @var Datetime */
    protected $model;

    /** @var Random|MockObject */
    protected $mathRandomMock;

    /** @var ResolverInterface|MockObject */
    protected $localeResolverMock;

    /** @var DateTimeFormatterInterface|MockObject */
    protected $dateTimeFormatterMock;

    /** @var Column|MockObject */
    protected $columnMock;

    /** @var TimezoneInterface|MockObject */
    protected $localeDateMock;

    /** @var Escaper|MockObject */
    private $escaperMock;

    /** @var Context|MockObject */
    private $contextMock;

    /**
     * @var Http|MockObject
     */
    private $request;

    /**
     * @var Repository|MockObject
     */
    private $repositoryMock;

    protected function setUp(): void
    {
        $this->mathRandomMock = $this->getMockBuilder(Random::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUniqueHash'])
            ->getMock();

        $this->localeResolverMock = $this->getMockBuilder(ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->dateTimeFormatterMock = $this
            ->getMockBuilder(DateTimeFormatterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->columnMock = $this->getMockBuilder(Column::class)
            ->disableOriginalConstructor()
            ->addMethods(['getTimezone','getFilterTime'])
            ->onlyMethods(['getHtmlId', 'getId'])
            ->getMock();

        $this->localeDateMock = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->once())->method('getEscaper')->willReturn($this->escaperMock);
        $this->contextMock->expects($this->once())->method('getLocaleDate')->willReturn($this->localeDateMock);

        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->repositoryMock = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUrlWithParams'])
            ->getMock();

        $this->contextMock->expects($this->once())
            ->method('getAssetRepository')
            ->willReturn($this->repositoryMock);

        $objectManagerHelper = new ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            Datetime::class,
            [
                'mathRandom' => $this->mathRandomMock,
                'localeResolver' => $this->localeResolverMock,
                'dateTimeFormatter' => $this->dateTimeFormatterMock,
                'localeDate' => $this->localeDateMock,
                'context' => $this->contextMock,
            ]
        );
        $this->model->setColumn($this->columnMock);
    }

    public function testGetHtmlSuccessfulTimestamp()
    {
        $uniqueHash = 'H@$H';
        $id = 3;
        $format = 'mm/dd/yyyy';
        $yesterday = new \DateTime();
        $yesterday->add(\DateInterval::createFromDateString('yesterday'));
        $tomorrow = new \DateTime();
        $tomorrow->add(\DateInterval::createFromDateString('tomorrow'));
        $value = [
            'locale' => 'en_US',
            'from' => $yesterday->getTimestamp(),
            'to' => $tomorrow->getTimestamp()
        ];
        $params = ['_secure' => false];
        $fileId = 'Magento_Theme::calendar.png';
        $fileUrl = 'file url';

        $this->repositoryMock->expects($this->once())
            ->method('getUrlWithParams')
            ->with($fileId, $params)
            ->willReturn($fileUrl);

        $this->mathRandomMock->expects($this->any())->method('getUniqueHash')->willReturn($uniqueHash);
        $this->columnMock->expects($this->once())->method('getHtmlId')->willReturn($id);
        $this->localeDateMock->expects($this->any())->method('getDateFormat')->willReturn($format);
        $this->columnMock->expects($this->any())->method('getTimezone')->willReturn(false);
        $this->localeResolverMock->expects($this->any())->method('getLocale')->willReturn('en_US');
        $this->model->setColumn($this->columnMock);
        $this->model->setValue($value);

        $output = $this->model->getHtml();
        $this->assertStringContainsString(
            'id="' . $uniqueHash . '_from" value="' . $yesterday->getTimestamp(),
            $output
        );
        $this->assertStringContainsString(
            'id="' . $uniqueHash . '_to" value="' . $tomorrow->getTimestamp(),
            $output
        );
    }

    public function testGetEscapedValueEscapeString()
    {
        $value = "\"><img src=x onerror=alert(2) />";
        $array = [
            'orig_from' => $value,
            'from' => $value,
        ];
        $this->model->setValue($array);
        $this->escaperMock->expects($this->once())->method('escapeHtml')->with($value);
        $this->columnMock->expects($this->once())->method('getFilterTime')->willReturn(true);
        $this->model->getEscapedValue('from');
    }
}
