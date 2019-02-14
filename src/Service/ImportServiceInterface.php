<?php

namespace App\Service;

/**
 * Interface for Harvest2GitlabImportService.
 *
 * @package App\Service
 */
interface ImportServiceInterface {

  /**
   * Import time entries of a given project.
   *
   * @param string $projectId
   *   The harvest project id.
   *
   * @return array
   *   Info array: Number of updated Time Entries.
   */
  public function importTimeEntries($projectId);

}
