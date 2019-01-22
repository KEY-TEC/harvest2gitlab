<?php

namespace App\Service;

use FH\HarvestApiClient\Endpoint\TimeEntryEndpoint;
use FH\HarvestApiClient\Endpoint\ProjectEndpoint;
use FH\HarvestApiClient\Client\ClientFactory;
use FH\HarvestApiClient\Model\Project\Project;
use JMS\Serializer\SerializerBuilder;

/**
 * Service for Harvest API.
 *
 * @package App\Service
 */
class HarvestService implements HarvestServiceInterface {

  /**
   * The Harvest project.
   *
   * @var \FH\HarvestApiClient\Endpoint\ProjectEndpoint
   */
  private $projectEndpoint;

  /**
   * Loaded projects.
   *
   * @var \FH\HarvestApiClient\Model\Project\Project[]
   */
  private $projects;

  /**
   * The Harvest time entry.
   *
   * @var \FH\HarvestApiClient\Endpoint\TimeEntryEndpoint
   */
  private $timeEntry;

  /**
   * HarvestService constructor.
   *
   * @param int $harvestAccountId
   *   The Harvest account id.
   * @param string $harvestAccessToken
   *   The Harvest API token.
   */
  public function __construct($harvestAccountId, $harvestAccessToken) {
    $clientConfiguration = [
      'account_id' => $harvestAccountId,
      'access_token' => $harvestAccessToken,
      'user_agent' => 'Harvest2Gitlab Importer (info@key-tec.de)',
    ];
    $client = ClientFactory::create([], NULL, NULL, $clientConfiguration);
    $serializer = SerializerBuilder::create()
      ->addMetadataDir(
        __DIR__
        . '/../../vendor/freshheads/harvest-api-client/src/Model/configuration'
      )->build();
    $this->projectEndpoint = new ProjectEndpoint($client, $serializer);
    $this->timeEntry = new TimeEntryEndpoint($client, $serializer);
  }

  /**
   * {@inheritdoc}
   */
  public function getValidTimeEntries(Project $harvestProject) {
    $all_time_entries = $this->fetchTimeEntriesRecursive($harvestProject);
    $external_reference = array();
    foreach ($all_time_entries as $timeEntry) {
      $permalink = $timeEntry->getExternalReference()['permalink'];
      if (empty($permalink)) {
        continue;
      }
      if (isset($external_reference[$permalink]) == FALSE) {
        $external_reference[$permalink] = $timeEntry->getHours();
      }
      else {
        $external_reference[$permalink] += $timeEntry->getHours();
      }
    }
    return $external_reference;
  }

  /**
   * Get or loads projects from endpoint.
   * @return array
   */
  protected function getProjects() {
    if (empty($this->projects)) {
      $this->projects = $this->projectEndpoint->list()->getProjects();
    }
    return $this->projects;
  }

  /**
   * {@inheritdoc}
   */
  public function getHarvestProjectByCode($project_code) {
    foreach ($this->getProjects() as $project) {
      if ($project_code == $project->getCode()) {
        return $project;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getHarvestProjectById($project_id) {
    foreach ($this->getProjects() as $project) {
      if ($project_id == $project->getId()) {
        return $project;
      }
    }
  }


  /**
   * Returns the date string from 30 days ago.
   *
   * @return false|string
   *   The Date YYYY-MM-DD.
   */
  private function getDate() {
    $timestamp = strtotime('-360 days');
    return date("c", $timestamp);
  }

  /**
   * Fetches all time entries of a project.
   *
   * @param \FH\HarvestApiClient\Model\Project\Project $harvestProject
   *   The Harvest project.
   * @param int $page
   *   The displayed page of time entries.
   *
   * @return \FH\HarvestApiClient\Model\TimeEntry\TimeEntry[]
   *   All the time entries.
   */
  private function fetchTimeEntriesRecursive(Project $harvestProject, $page = 1) {
    /* @var TimeEntryCollection $timeEntryCollection */
    $timeEntryCollection = $this->timeEntry->list([
      'updated_since' => $this->getDate(),
      'project_id' => $harvestProject->getId(),
      'page' => $page,
    ]);

    $timeEntries = $timeEntryCollection->getTimeEntries();

    if ($page < $timeEntryCollection->getTotalPages()) {
      $timeEntries = array_merge($timeEntries, $this->fetchTimeEntriesRecursive($harvestProject, $page += 1));
    }
    return $timeEntries;
  }

}
