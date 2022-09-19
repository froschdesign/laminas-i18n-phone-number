<?php

declare(strict_types=1);

namespace LaminasTest\I18n\PhoneNumber;

use Laminas\I18n\PhoneNumber\CountryCode;
use Laminas\I18n\PhoneNumber\Exception\InvalidArgumentException;
use Locale;
use PHPUnit\Framework\TestCase;

class CountryCodeTest extends TestCase
{
    private string $preserveLocale;

    protected function setUp(): void
    {
        parent::setUp();
        $this->preserveLocale = Locale::getDefault();
    }

    protected function tearDown(): void
    {
        Locale::setDefault($this->preserveLocale);
        parent::tearDown();
    }

    public function testCountryCodesAreNormalisedToUppercase(): void
    {
        $code = CountryCode::fromString('gb');
        self::assertEquals('GB', $code->toString());
    }

    public function testMixedCaseCodesAreConsideredEqual(): void
    {
        self::assertTrue(
            CountryCode::fromString('GB')->equals(
                CountryCode::fromString('gB')
            )
        );
    }

    public function testDifferentCodesAreNotConsideredEqual(): void
    {
        self::assertFalse(
            CountryCode::fromString('ZA')->equals(
                CountryCode::fromString('US')
            )
        );
    }

    public function testPatternMatchFailureForCountryCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Country codes should be 2 letter ISO 3166 strings');
        CountryCode::fromString('Wrong');
    }

    public function testInvalidCountryCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The country code "ZZ" does not correspond to a known country');
        CountryCode::fromString('ZZ');
    }

    /** @return list<array{0:non-empty-string, 1:non-empty-string}> */
    public function localeToCountryCodeDataProvider(): array
    {
        return [
            ['sl-Latn-IT-nedis', 'IT'],
            ['en_GB', 'GB'],
            ['en-US', 'US'],
            ['en-US.utf8', 'US'],
            ['FR-fr@EURO', 'FR'],
        ];
    }

    /**
     * @param non-empty-string $input
     * @param non-empty-string $expect
     * @dataProvider localeToCountryCodeDataProvider
     */
    public function testCountryCodesCanBeConstructedFromLocales(string $input, string $expect): void
    {
        $code = CountryCode::fromLocale($input);
        self::assertEquals($expect, $code->toString());
    }

    public function testAnInvalidLocaleWillCauseAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The string "Wrong" could not be parsed as a valid locale');
        CountryCode::fromLocale('Wrong');
    }

    /** @return list<array{0:non-empty-string, 1:non-empty-string}> */
    public function detectProvider(): array
    {
        return [
            ['sl-Latn-IT-nedis', 'IT'],
            ['en_GB', 'GB'],
            ['en-US', 'US'],
            ['en-US.utf8', 'US'],
            ['FR-fr@EURO', 'FR'],
            ['za', 'ZA'],
            ['-DE', 'DE'],
            ['us', 'US'],
        ];
    }

    /**
     * @param non-empty-string $input
     * @param non-empty-string $expect
     * @dataProvider detectProvider
     */
    public function testThatDetectWillCorrectlyIdentifyTheCountryCode(string $input, string $expect): void
    {
        $code = CountryCode::detect($input);
        self::assertEquals($expect, $code->toString());
    }

    public function testDetectWillThrowAnExceptionIfTheInputCannotBeUnderstood(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The string "Wrong" could not be understood as either a locale or an ISO 3166 country code'
        );
        CountryCode::detect('Wrong');
    }

    public function testDetectAcceptsCountryCodeInstance(): void
    {
        $input = CountryCode::fromString('UA');
        self::assertSame($input, CountryCode::detect($input));
    }

    public function testDetectWillUseDefaultLocaleForEmptyInput(): void
    {
        Locale::setDefault('en_GB');
        $code = CountryCode::detect(null);
        self::assertEquals('GB', $code->toString());
        $code = CountryCode::detect('');
        self::assertEquals('GB', $code->toString());
    }
}
