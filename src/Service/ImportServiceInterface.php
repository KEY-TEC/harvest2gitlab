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
   * @param string $project_path_with_namespace
   *   The project code.
   *
   * @return mixed
   *   Number of updated Time Entries.
   */
  public function importTimeEntries($project_path_with_namespace);

}
