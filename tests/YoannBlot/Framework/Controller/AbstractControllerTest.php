<?php

namespace YoannBlot\Framework\Controller;

use YoannBlot\Framework\Helper\Reflection;

/**
 * Class AbstractControllerTest
 *
 * @package YoannBlot\Framework\Controller
 */
class AbstractControllerTest extends \PHPUnit_Framework_TestCase {

    const VALID_PAGE = 'valid';
    const INVALID_PAGE = 'fakeXC';

    /**
     * Test to get current page.
     */
    public function testGetCurrentPage () {
        $oController = new FakeController();
        $this->assertNotEmpty($oController->getCurrentPage());
        $this->assertEquals(AbstractController::DEFAULT_PAGE, $oController->getCurrentPage());

        // invalid page
        $oController->setCurrentPage(static::INVALID_PAGE);
        $this->assertEquals(AbstractController::DEFAULT_PAGE, $oController->getCurrentPage());

        // valid page
        $oController->setCurrentPage(static::VALID_PAGE);
        $this->assertEquals(static::VALID_PAGE, $oController->getCurrentPage());
    }

    /**
     * Test get view directory method.
     */
    public function testGetViewDirectory () {
        $oController = new FakeController();

        $sViewDirectory = Reflection::getValue($oController, "getViewDirectory");

        $this->assertNotNull($sViewDirectory);
        $this->assertNotEmpty($sViewDirectory);
        $this->assertContains(AbstractController::VIEW_DIR_NAME, $sViewDirectory);
        $this->assertContains(AbstractController::TEMPLATE_EXT, $sViewDirectory);
    }

    public function testIsValidPage () {
        $oController = new FakeController();

        $oMethod = Reflection::getMethod($oController, "isValidPage");

        $this->assertFalse($oMethod->invoke($oController, static::INVALID_PAGE));
        $this->assertTrue($oMethod->invoke($oController, static::VALID_PAGE));
    }

    public function testGetDefaultPage () {
        $oController = new FakeController();

        $aValidPageData = Reflection::getValue($oController, "getPageData");
        $this->assertEmpty($aValidPageData);
    }

    public function testGetPage () {
        $oController = new FakeController();
        $oController->setCurrentPage('valid');

        $aValidPageData = Reflection::getValue($oController, "getPageData");
        $this->assertNotEmpty($aValidPageData);
        $this->assertArrayHasKey('title', $aValidPageData);
    }
}
