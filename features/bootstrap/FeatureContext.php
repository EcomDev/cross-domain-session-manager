<?php

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\MinkExtension\Context\MinkContext;
use PHPUnit\Framework\Assert;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext
{
    private $urls = [];
    private $assignedSessions = [];
    private $assignedDomainSesssions = [];

    private $sessionCookieName;

    const RANDOM_32_HEX_CHARACTERS = '~^[0-9a-fA-F]{32}~';
    /**
     * @var string[]
     */
    private $minkSessions;

    private $actorSessions = [];

    public function __construct(array $sessionPool, $sessionCookieName, array $urls)
    {
        $this->urls = $urls;
        $this->sessionCookieName = $sessionCookieName;
        $this->minkSessions = $sessionPool;
    }

    /**
     * Returns actor mink session
     *
     * @param string $visitor
     *
     * @return \Behat\Mink\Session
     */
    private function getActorSession($visitor)
    {
        if (!isset($this->actorSessions[$visitor])) {
            $this->actorSessions[$visitor] = array_shift($this->minkSessions);
        }

        return $this->getSession($this->actorSessions[$visitor]);
    }

    /**
     * @Given I have a visitor :visitor
     */
    public function iHaveAVisitor($visitor)
    {
        $session = $this->getActorSession($visitor);

        if (!$session->isStarted()) {
            $session->start();
        } else {
            $session->reset();
        }
    }

    /**
     * @When :visitor visits session script
     */
    public function visitsAPageWithScript($visitor)
    {
        $this->getActorSession($visitor)->visit($this->urls['script']);
    }

    /**
     * @Then :visitor gets new session ID assigned
     */
    public function getsNewSessionIdAssigned($visitor)
    {
        Assert::assertThat(
            $this->fetchSessionIdForVisitor($visitor),
            Assert::logicalAnd(
                Assert::matchesRegularExpression(
                    self::RANDOM_32_HEX_CHARACTERS
                ),
                Assert::logicalNot(
                    Assert::equalTo($this->assignedSessions[$visitor] ?? '')
                )
            )
        );
    }

    /**
     * @Given :visitor has session ID assigned
     */
    public function hasSessionIdAssigned($visitor)
    {
        $this->visitsAPageWithScript($visitor);
        $this->assignedSessions[$visitor] = $this->fetchSessionIdForVisitor($visitor);
    }

    /**
     * @Then :visitor has session ID as initially
     */
    public function hasSessionIdAsInitially($visitor)
    {
        Assert::assertEquals(
            $this->assignedSessions[$visitor],
            $this->fetchSessionIdForVisitor($visitor)
        );
    }

    /**
     * @Given :visitor has unregistered session ID
     */
    public function hasUnregisteredSessionId($visitor)
    {
        $this->hasSessionIdAssigned($visitor);

        $sessionId = bin2hex(random_bytes(16));

        $this->getActorSession($visitor)->setCookie(
            $this->sessionCookieName,
            $sessionId
        );

        $this->assignedSessions[$visitor] = $sessionId;
    }

    /**
     * @Then :visitorOne different session ID than :visitorTwo
     */
    public function differentSessionIdThan($visitorOne, $visitorTwo)
    {
        Assert::assertNotEquals(
            $this->fetchSessionIdForVisitor($visitorOne),
            $this->fetchSessionIdForVisitor($visitorTwo)
        );
    }


    /**
     * @When :visitor visits home page on :domain
     */
    public function visitsHomePageOn($visitor, $domain)
    {
        $this->getActorSession($visitor)->visit($this->urls[$domain]);
        $this->assignedDomainSesssions[$visitor][$domain] = $this->fetchSessionIdForVisitor($visitor);
    }

    /**
     * @Then :visitor has valid Session ID on :domain as for session script
     */
    public function hasValidSessionIdOnAsForSessionScript($visitor, $domain)
    {
        Assert::assertEquals(
            $this->assignedSessions[$visitor],
            $this->assignedDomainSesssions[$visitor][$domain]
        );
    }

    /**
     * @Then :visitor has same Session ID for :domainOne and :domainTwo
     */
    public function hasSameSessionIdAsOnAnotherDomain($visitor, $domainOne, $domainTwo)
    {
        Assert::assertEquals(
            $this->assignedDomainSesssions[$visitor][$domainOne],
            $this->assignedDomainSesssions[$visitor][$domainTwo]
        );
    }

    /**
     * @When :visitor does not interact with website for :seconds seconds
     */
    public function doesNotInteractWithWebsite($visitor, $seconds)
    {
        $this->getActorSession($visitor)->wait($seconds*1000);
    }

    private function fetchSessionIdForVisitor($visitor, $url = null)
    {
        $cookies = $this->getActorSession($visitor)->getDriver()->getWebDriverSession()->getAllCookies();

        $url = $url ?: $this->getActorSession($visitor)->getCurrentUrl();

        $domain = parse_url($url, PHP_URL_HOST);

        foreach ($cookies as $cookie) {
            if ($cookie['name'] === $this->sessionCookieName && $this->matchDomain($domain, $cookie['domain'])) {
                return urldecode($cookie['value']);
            }
        }

        return null;
    }

    private function matchDomain($urlDomain, $cookieDomain)
    {
        return $urlDomain === ltrim($cookieDomain, '.') || substr($urlDomain, -strlen($cookieDomain)) === $cookieDomain;
    }
}
