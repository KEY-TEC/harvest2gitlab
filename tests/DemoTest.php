<?php
/**
 * Created by PhpStorm.
 * User: valentinmeyer
 * Date: 18.01.19
 * Time: 12:39
 */

class DemoTest extends \PHPUnit\Framework\TestCase {
  public function testTest() {

    self::bootKernel();
    $container = self::$kernel->getContainer();
    $ImportService = $container->get('ImportService');
    $this->assertEquals(TRUE, TRUE);
  }
}