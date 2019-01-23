<?php

namespace App\Service;

/**
 * Interface for GitlabService.
 *
 * @package App\Service
 */
interface GitlabServiceInterface {

  /**
   * Checks if issue time changed and if so it sets the new time.
   *
   * @param int $project_id
   *   The project id.
   * @param string $issue_id
   *   The issue id.
   * @param int $hours
   *   The tracked hours of a ticket in decimal.
   *   Eg 1.34
   *
   * @return mixed
   *   Returns int of changed time entries.
   */
  public function saveTimeSpend($project_id, $issue_id, $hours);

  /**
   * Returns project by Harvest path with namespace.
   *
   * @param string $projectPathWithNamespace
   *   The path with namespace form Harvest.
   *
   * @return array
   *   The matching project.
   */
  public function getProjectByHarvest($projectPathWithNamespace);

  /**
   * Returns milestones.
   *
   * @param int $projectId
   *   The project id.
   *
   * @return array
   *   The milestones.
   */
  public function getMilestones($projectId);

  /**
   * Returns the current milestone.
   *
   * @param int $projectId
   *   The project id.
   * @param int $milestoneId
   *   The milestone id.
   *
   * @return mixed
   *   The Milestone.
   */
  public function getCurrentMilestone($projectId, $milestoneId);

  /**
   * Returns Gitlab projects.
   *
   * @return array
   *   The projects.
   */
  public function getProjects();

  /**
   * Returns project by its id.
   *
   * @param int $projectId
   *   The project id.
   *
   * @return mixed
   *   The project.
   */
  public function getProject($projectId);

  /**
   * Returns all issues of a milestone.
   *
   * @param int $projectId
   *   The project id.
   * @param int $milestoneId
   *   The milestone id.
   *
   * @return array
   *   The milestones issues.
   */
  public function getMilestoneIssues($projectId, $milestoneId);

}
