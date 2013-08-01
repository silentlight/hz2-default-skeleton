<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Event\SuiteEvent,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;


//
// Require 3rd-party libraries here:
//
require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
require_once 'PHPUnit/Extensions/Selenium2TestCase.php';
use Behat\MinkExtension\Context\MinkContext;

//bootstrap zend framework
require_once dirname(__FILE__) . '/TestBootstrap.php';

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
	public $app;
	
    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        $this->app = TestBootstrap::getApp();
	
		//add other contexts
		$this->useContext('UI', new UIContext($parameters));
		$this->useContext('ModuleTransformer', new ModuleTransformer($parameters, $this->app));
    }
	
	/** @BeforeSuite */
	public static function setup(SuiteEvent $event)
	{
		 $app = TestBootstrap::getApp();
		 
		 $app->beforeSuite();
	}
	
	/** @AfterSuite */
	public static function teardown(SuiteEvent $event)
	{
		$app = TestBootstrap::getApp();
		 
		$app->afterSuite();
	}
	
	/********************************************************
	 * 
	 * 	STEP DEFINITIONS
	 * 
	 *******************************************************/
	 
    /**
     * @Then /^the module should be "([^"]*)"$/
     */
    public function theModuleShouldBe($desiredModule) {
        $this->app->assertModule($desiredModule);
    }

    /**
     * @Given /^the controller should be "([^"]*)"$/
     */
    public function theControllerShouldBe($desiredController) {
        $this->app->assertController($desiredController);
    }

    /**
     * @Given /^the action should be "([^"]*)"$/
     */
    public function theActionShouldBe($desiredAction) {
        $this->app->assertAction($desiredAction);
    }

    /**
     * @Given /^the page should contain a "([^"]*)" tag that contains "([^"]*)"$/
     */
    public function thePageShouldContainATagThatContains($tag, $content) {
        $this->app->assertQueryContentContains($tag, $content);
    }

    /**
     * @Given /^the action should not redirect$/
     */
    public function theActionShouldNotRedirect() {
        $this->app->assertNotRedirect();
    }
	
}
