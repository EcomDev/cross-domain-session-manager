Feature: Generation of Session ID for visitors
  As a visitor
  I want to receive a unique session id
  In order to interact with website between requests

  Scenario: New Visitor Receives Unique Id
    Given I have a visitor "Bob"
    When "Bob" visits session script
    Then "Bob" gets new session ID assigned

  Scenario: Returning Visitor Gets Previous ID
    Given I have a visitor "Bob"
      And "Bob" has session ID assigned
    When "Bob" visits session script
    Then "Bob" has session ID as initially

  Scenario: Returning Visitor Has Unregistered Session ID
    Given I have a visitor "Bob"
      And "Bob" has unregistered session ID
    When "Bob" visits session script
    Then "Bob" gets new session ID assigned

  Scenario: Visitor sessions are unique
    Given I have a visitor "John"
      And I have a visitor "Bob"
    When "Bob" visits session script
      And "John" visits session script
    Then "John" different session ID than "Bob"

  Scenario: Visitor session expire
    Given I have a visitor "Bob"
      And "Bob" has session ID assigned
    When "Bob" does not interact with website for "5" seconds
      And "Bob" visits session script
    Then "Bob" gets new session ID assigned
