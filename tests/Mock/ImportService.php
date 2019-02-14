<?php

namespace App\Tests\Mock;

use App\Service\ImportServiceInterface;

/**
 * Mock for import sevrice.
 *
 * @package App\Service
 */
class ImportService implements ImportServiceInterface {

  /**
   * Imports HarvestService.
   *
   * @var \App\Service\HarvestServiceInterface
   */
  private $harvestService;

  /**
   * Imports GitlabService.
   *
   * @var \App\Service\GitlabServiceInterface
   */
  private $gitlabService;

  /**
   * Harvest2GitlabImportService constructor.
   *
   * @param \App\Service\HarvestServiceInterface $harvestService
   *   Harvest Service.
   * @param \App\Service\GitlabServiceInterface $gitlabService
   *   Gitlab Service.
   */
  /*public function __construct(HarvestServiceInterface $harvestService, GitlabServiceInterface $gitlabService) {
    $this->harvestService = $harvestService;
    $this->gitlabService = $gitlabService;
  }*/

  /**
   * Matches Harvest time entry with Gitlab issue.
   */
  public function importTimeEntries($projectId) {
    return [1, 2, 'fooooo'];
  }

}
