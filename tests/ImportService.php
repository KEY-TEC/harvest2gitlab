<?php

namespace App\Tests\Util;

use App\Service\ImportService;
use PHPUnit\Framework\TestCase;

class ImportServiceTest extends TestCase
{
  public function testImportEntries()
  {
    $calculator = new ImportService();
    $result = $calculator->add(30, 12);

  }
}