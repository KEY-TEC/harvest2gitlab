<?php

namespace App\Service;

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
   * @param string $projectPathWithNamespace
   *   The path with namespace form Harvest.
   *
   * @return int
   *   The updated number of updated time entries.
   */
  public function importTimeEntries($projectPathWithNamespace) {
    $harvest_project = $this->harvestService->getHarvestProjectByCode($projectPathWithNamespace);
    $time_entries = $this->harvestService->getValidTimeEntries($harvest_project);
    $gitlab_project = $this->gitlabService->getProjectByHarvest($projectPathWithNamespace);

    $updated_time_entries = 0;
    foreach ($time_entries as $key => $time_entry) {
      $parts = explode('/', $key);
      $id = $parts[count($parts) - 1];
      $check_time_estimate = $this->gitlabService->saveTimeEstimate($gitlab_project['id'], $id, $time_entry);
      if ($check_time_estimate == TRUE) {
        $updated_time_entries++;
      }
    }
    return $updated_time_entries;
  }

}
