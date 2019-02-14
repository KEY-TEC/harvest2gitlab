<?php

namespace App\Service;

use Gitlab\Exception\RuntimeException;

/**
 * Service to match Harvest time entries with Gitlab issues.
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
  public function __construct(HarvestServiceInterface $harvestService, GitlabServiceInterface $gitlabService) {
    $this->harvestService = $harvestService;
    $this->gitlabService = $gitlabService;
  }

  /**
   * Matches Harvest time entry with Gitlab issue.
   *
   * @param string $projectId
   *   The harvest project id.
   *
   * @return array
   *   Info:
   *    0 = all issues
   *    1 = updated issues
   *    2 = failed
   */
  public function importTimeEntries($projectId) {
    $harvest_project = $this->harvestService->getProjectById($projectId);
    $time_entries = $this->harvestService->getTimeEntriesWithReferences($harvest_project);
    $fails = [];
    $updated = [];

    foreach ($time_entries as $issue) {
      $reference = $issue[0]->getExternalReference()['id'];
      $reference = explode('#', $reference);

      try {
        $hours = 0;
        foreach ($issue as $time_entry) {
          $hours += $time_entry->getHours();
        }

        if ($this->gitlabService->saveTimeSpend($reference[0], $reference[1], $hours)) {
          $updated[] = $issue[0]->getExternalReference();
        }
      }
      catch (RuntimeException $e) {
        // project not found
        $fails[] = $issue[0]->getExternalReference();
        continue;
      }
    }

    return [count($time_entries), $updated, $fails];
  }

}
