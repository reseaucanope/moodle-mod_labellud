@mod @mod_labellud

Feature: Check labellud visibility works
  In order to check labellud visibility works
  As a teacher
  I should create labellud activity

  @javascript
  Scenario: Hidden labellud activity should be show as hidden.
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Test | C1 | 0 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher | Teacher | Frist | teacher1@example.com |
      | student | Student | First | student1@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher | C1 | editingteacher |
      | student | C1 | student |
    Given I log in as "teacher"
    And I am on "Test" course homepage with editing mode on
    When I add a "labellud" to section "1" and I fill the form with:
      | Labellud text | Swanky labellud |
      | Availability | Hide from students |
    Then "Swanky labellud" activity should be hidden
    And I turn editing mode off
    And "Swanky labellud" activity should be hidden
    And I log out
    And I log in as "student"
    And I am on "Test" course homepage
    And I should not see "Swanky labellud"
    And I log out

  @javascript
  Scenario: Visible labellud activity should be shown as visible.
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Test | C1 | 0 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher | Teacher | Frist | teacher1@example.com |
      | student | Student | First | student1@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher | C1 | editingteacher |
      | student | C1 | student |
    Given I log in as "teacher"
    And I am on "Test" course homepage with editing mode on
    When I add a "labellud" to section "1" and I fill the form with:
      | Labellud text | Swanky labellud |
      | Availability | Show on course page |
    Then "Swanky labellud" activity should be visible
    And I log out
    And I log in as "student"
    And I am on "Test" course homepage
    And "Swanky labellud" activity should be visible
    And I log out

  @javascript
  Scenario: Teacher can not show labellud inside the hidden section
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Test | C1 | 0 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher | Teacher | Frist | teacher1@example.com |
      | student | Student | First | student1@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher | C1 | editingteacher |
      | student | C1 | student |
    Given I log in as "teacher"
    And I am on "Test" course homepage with editing mode on
    When I add a "labellud" to section "1" and I fill the form with:
      | Labellud text | Swanky labellud |
      | Availability | Show on course page |
    And I hide section "1"
    Then "Swanky labellud" activity should be dimmed
    And I open "Swanky labellud" actions menu
    And "Swanky labellud" actions menu should not have "Show" item
    And "Swanky labellud" actions menu should not have "Hide" item
    And "Swanky labellud" actions menu should not have "Make available" item
    And "Swanky labellud" actions menu should not have "Make unavailable" item
    And I click on "Edit settings" "link" in the "Swanky labellud" activity
    And I expand all fieldsets
    And the "Availability" select box should contain "Hide from students"
    And the "Availability" select box should not contain "Make available but not shown on course page"
    And the "Availability" select box should not contain "Show on course page"
    And I log out
    And I log in as "student"
    And I am on "Test" course homepage
    And I should not see "Swanky labellud"
    And I log out
