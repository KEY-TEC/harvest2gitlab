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
   * Returns projects from harvest.
   *
   * @return array
   *   Harvest projects.
   */
  public function getProjects();

  /**
   * Returns time entries, filtered and grouped by external references.
   *
   * @param \FH\HarvestApiClient\Model\Project\Project $harvestProject
   *   The Harvest project.
   *
   * @return []
   *   All valid time entries.
   */
  public function getTimeEntriesWithReferences(Project $harvestProject);

  /**
   * Returns the Harvest project by its project code.
   *
   * @param string $project_id
   *   The project id.
   *
   * @return Project
   *   The project.
   */
  public function getProjectById($project_id);

  /**
   * Returns the Harvest project by its project code.
   *
   * @param string $project_code
   *   The project code.
   *
   * @return Project
   *   The project.
   */
  public function getProjectByCode($project_code);

}
