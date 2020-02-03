<?php

namespace App\Service;

use Gitlab\Client;

/**
 * Service for Gitlab API.
 *
 * @package App\Service
 */
class GitlabService implements GitlabServiceInterface {

  /**
   * The Gitlab Url.
   *
   * @var string
   */
  private $gitUrl;

  /**
   * The Gitlab API token.
   *
   * @var string
   */
  private $gitToken;

  /**
   * The Gitlab Client.
   *
   * @var \Gitlab\Client
   * The Gitlab client.
   */
  private $client;

  /**
   * GitlabService constructor.
   *
   * @param string $gitUrl
   *   Url of Gitlab.
   * @param string $gitToken
   *   Gitlab API token.
   */
  public function __construct($gitUrl, $gitToken) {
    $this->gitUrl = $gitUrl;
    $this->gitToken = $gitToken;
  }

  /**
   * Returns Gitlab client.
   *
   * @return \Gitlab\Client
   *   The client.
   */
  private function getClient() {
    if (empty($this->client)) {
      $this->client = Client::create($this->gitUrl)->authenticate($this->gitToken, Client::AUTH_URL_TOKEN);
    }
    return $this->client;
  }

  /**
   * {@inheritdoc}
   */
  public function getProjects() {
    $client = $this->getClient();
    $projects = $client->projects()->all();
    return [
      "Projects" => $projects,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getProject($projectId) {
    $client = $this->getClient();
    $project = $client->projects()->show($projectId);
    return $project;
  }

  /**
   * {@inheritdoc}
   */
  public function getMilestones($projectId) {
    $client = $this->getClient();
    $milestones = $client->milestones()->all($projectId);
    return $milestones;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentMilestone($projectId, $milestoneId) {
    $client = $this->getClient();
    $currentMilestone = $client->milestones()->show($projectId, $milestoneId);
    return $currentMilestone;
  }

  /**
   * {@inheritdoc}
   */
  public function getMilestoneIssues($projectId, $milestoneId) {
    $client = $this->getClient();
    $milestoneIssues = $client->milestones()->issues($projectId, $milestoneId);
    return $milestoneIssues;
  }

  /**
   * {@inheritdoc}
   */
  public function saveTimeSpend($project_id, $issue_id, $hours) {
    $issues = $this->getClient()->issues();
    $exploded_project_id = explode("/", $project_id);
    if ($exploded_project_id[0] == "") {
      $project_id_untrimmed = $project_id;
      $project_id = ltrim($project_id_untrimmed, "/");
    }
    $old_time = $issues->getTimeStats($project_id, $issue_id)['total_time_spent'];
    $new_time = abs($hours * 3600);
    // only need update on more than 5 minutes difference
    $x = abs($new_time - $old_time);
    if (abs($new_time - $old_time) >= 300) {
      $time_spend = floor($hours) . 'h' . floor(($hours - floor($hours)) * 60) . 'm';
      $issues->resetSpentTime($project_id, $issue_id);
      $issues->addSpentTime($project_id, $issue_id, $time_spend);
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getProjectByHarvest($projectPathWithNamespace) {
    $gitlab_projects = $this->getProjects()['Projects'];
    $gitlab_project = [];
    foreach ($gitlab_projects as $project) {
      if ($project['path_with_namespace'] == $projectPathWithNamespace) {
        $gitlab_project = $project;
        break;
      }
    }
    return $gitlab_project;
  }

}
