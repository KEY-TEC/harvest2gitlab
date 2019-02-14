<?php

namespace App\Tests\Command;

use App\Tests\Service\ImportService;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class HarvestTimeExportCommandTest extends KernelTestCase {

  public function testExecute() {
    /*self::bootKernel();
    $container = self::$kernel->getContainer();
    $mockedImportService = new ImportService();
    $container->set('ImportServiceInterface', $mockedImportService);
    $application = new Application($kernel);

    $command = $application->find('app:harvest:time-export');
    $commandTester = new CommandTester($command);
    $commandTester->execute([
      'command'  => $command->getName(),

      // pass arguments to the helper
      'harvest project id' => '123',
    ]);

    // the output of the command in the console
    $output = $commandTester->getDisplay();
    $this->assertContains('Username: Wouter', $output);*/

    // ...
  }
}