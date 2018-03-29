Feature: Sharing of Session ID between domains
  As a visitor
  I want to have the same session id across multiple domains of the same website
  In order to enter my credentials only once per session

  Scenario Outline: Visitors receives Session ID from parent domain
    Given I have a visitor "Bob"
    When "Bob" has session ID assigned
     And "Bob" visits home page on <domain>
    Then "Bob" has valid Session ID on <domain> as for session script
  Examples:
    | domain       |
    | domain_one   |
    | domain_two   |

  Scenario: Visitors receives Session ID on child domain
    Given I have a visitor "Bob"
    When "Bob" visits home page on "domain_one"
     And "Bob" visits home page on "domain_two"
    Then "Bob" has same Session ID for "domain_one" and "domain_two"
