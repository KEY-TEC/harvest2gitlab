<?php

namespace App\Service;

use FH\HarvestApiClient\Model\Project\Project;

/**
 * Interface for HarvestService.
 *
 * @package App\Service
 */
interface HarvestServiceInterface {

  /**
   * Returns all valid time entries of a project.
   *
   * @param \FH\HarvestApiClient\Model\Project\Project $harvestProject
   *   The Harvest project.
   *
   * @return mixed
   *   All the valid time entries.
   */
  public function getValidTimeEntries(Project $harvestProject);

  /**
   * Returns the Harvest project by its project code.
   *
   * @param string $project_code
   *   The project code.
   *
   * @return mixed
   *   The project.
   */
  public function getHarvestProjectByCode($project_code);

}
