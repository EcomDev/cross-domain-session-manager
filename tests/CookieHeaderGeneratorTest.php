<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;


use PHPUnit\Framework\TestCase;

class CookieHeaderGeneratorTest extends TestCase
{
    /** @var CookieHeaderGeneratorFactory */
    private $generatorFactory;

    protected function setUp()
    {
        $this->generatorFactory = new CookieHeaderGeneratorFactory(
            new FakeTimeManager(new \DateTimeImmutable('2010-01-28T15:00:00+02:00'))
        );
    }

    /** @test */
    public function defaultCookieGeneration()
    {
        $this->assertEquals(
            'someCookie=someValue',
            $this->generatorFactory
                ->createGenerator()
                ->generateCookie('someCookie', 'someValue')
        );
    }

    /** @test */
    public function cookieWithEscapedValue()
    {
        $this->assertEquals(
            'some%2Fcookie=some%2Fvalue%2Fwith%2Fslashes',
            $this->generatorFactory
                ->createGenerator()
                ->generateCookie('some/cookie', 'some/value/with/slashes')
        );
    }

    /** @test */
    public function cookieWithPath()
    {
        $this->assertEquals(
            'someCookie=someValue; Path=/',
            $this->generatorFactory
                ->createGenerator()
                ->generateCookie('someCookie', 'someValue', ['path' => '/'])
        );
    }

    /** @test */
    public function cookieWithDomain()
    {
        $this->assertEquals(
            'someCookie=someValue; Domain=domain.com',
            $this->generatorFactory
                ->createGenerator()
                ->generateCookie('someCookie', 'someValue', ['domain' => 'domain.com'])
        );
    }

    /** @test */
    public function cookieRequestWithSubDomainResultsInBaseDomainAssignment()
    {
        $this->assertEquals(
            'someCookie=someValue; Domain=domain.com',
            $this->generatorFactory
                ->createGeneratorWithOptions(['domain' => 'domain.com'])
                ->generateCookie('someCookie', 'someValue', ['domain' => 'sub.domain.com'])
        );
    }

    /** @test */
    public function cookieRequestWithSimilarDomainButBreaksSameDomainPolicyDoesNotAssignBaseDomain()
    {
        $this->assertEquals(
            'someCookie=someValue; Domain=sub-domain.com',
            $this->generatorFactory
                ->createGeneratorWithOptions(['domain' => 'domain.com'])
                ->generateCookie('someCookie', 'someValue', ['domain' => 'sub-domain.com'])
        );
    }

    /** @test */
    public function cookieRequestWithPrefixedDomainButDifferentBaseFallbackToPrefixedDomain()
    {
        $this->assertEquals(
            'someCookie=someValue; Domain=test.domain.com.example.org',
            $this->generatorFactory
                ->createGeneratorWithOptions(['domain' => 'domain.com'])
                ->generateCookie('someCookie', 'someValue', ['domain' => 'test.domain.com.example.org'])
        );
    }

    /** @test */
    public function cookieRequestWithDifferentDomainResultsInUsignOriginalOne()
    {
        $this->assertEquals(
            'someCookie=someValue; Domain=test.com',
            $this->generatorFactory
                ->createGeneratorWithOptions(['domain' => 'domain.com'])
                ->generateCookie('someCookie', 'someValue', ['domain' => 'test.com'])
        );
    }


    /** @test */
    public function cookieWithLifetime()
    {
        $this->assertEquals(
            'someCookie=someValue; Expires=Thu, 28 Jan 2010 16:00:00 +0200; Max-Age=3600',
            $this->generatorFactory
                ->createGenerator()
                ->generateCookie('someCookie', 'someValue', ['lifetime' => 3600])
        );
    }

    /** @test */
    public function cookieWithAllOptions()
    {
        $this->assertEquals(
            'someCookie=someValue; Expires=Thu, 28 Jan 2010 16:00:00 +0200; '
            . 'Max-Age=3600; Path=/; Domain=domain.com; HttpOnly; Secure',
            $this->generatorFactory
                ->createGenerator()
                ->generateCookie('someCookie', 'someValue', [
                    'lifetime' => 3600,
                    'path' => '/',
                    'domain' => 'domain.com',
                    'secure' => true,
                    'http-only' => true
                ])
        );
    }

    /** @test */
    public function defaultOptionsAffectCookieOptionsDuringGeneration()
    {
        $this->assertEquals(
            'someCookie=someValue; Expires=Thu, 28 Jan 2010 16:00:00 +0200; '
            . 'Max-Age=3600; Path=/; Domain=domain.com; HttpOnly; Secure',
            $this->generatorFactory
                ->createGeneratorWithOptions([
                    'lifetime' => 3600,
                    'secure' => true,
                    'http-only' => true,
                    'domain' => 'domain.com'
                ])
                ->generateCookie('someCookie', 'someValue', [
                    'path' => '/'
                ])
        );
    }

    /** @test */
    public function defaultOptionsHaveLessPriorityThanInlineOptions()
    {
        $this->assertEquals(
            'someCookie=someValue; Expires=Thu, 28 Jan 2010 16:00:00 +0200; '
            . 'Max-Age=3600; Path=/; Domain=domain.com; HttpOnly; Secure',
            $this->generatorFactory
                ->createGeneratorWithOptions([
                    'lifetime' => 3600,
                    'secure' => true,
                    'domain' => 'domain-another.com',
                    'http-only' => true
                ])
                ->generateCookie('someCookie', 'someValue', [
                    'path' => '/',
                    'domain' => 'domain.com'
                ])
        );
    }

    /** @test */
    public function preservesWwwInBaseDomainByDefault()
    {
        $this->assertEquals(
            'someCookie=someValue; Domain=www.domain.com',
            $this->generatorFactory
                ->createGeneratorWithOptions([
                    'domain' => 'www.domain.com',
                ])
                ->generateCookie('someCookie', 'someValue')
        );
    }

    /** @test */
    public function trimsWwwInBaseDomainName()
    {
        $this->assertEquals(
            'someCookie=someValue; Domain=domain.com',
            $this->generatorFactory
                ->createGeneratorWithOptions([
                    'domain' => 'www.domain.com',
                    'trim-www' => true
                ])
                ->generateCookie('someCookie', 'someValue')
        );
    }

    /** @test */
    public function trimsWwwInMainDomainName()
    {
        $this->assertEquals(
            'someCookie=someValue; Domain=domain.com',
            $this->generatorFactory
                ->createGeneratorWithOptions([
                    'trim-www' => true
                ])
                ->generateCookie('someCookie', 'someValue', [
                    'domain' => 'www.domain.com'
                ])
        );
    }
}
