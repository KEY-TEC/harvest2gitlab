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
  public function getTimeEntriesWithReferences(Project $harvestProject) {
    $all_time_entries = $this->fetchTimeEntriesRecursive($harvestProject);
    $timeEntries = [];
    foreach ($all_time_entries as $timeEntry) {
      $permalink = $timeEntry->getExternalReference()['permalink'];
      if (empty($permalink)) {
        continue;
      }
      if (isset($external_reference[$permalink]) == FALSE) {
        $timeEntries[$permalink][] = $timeEntry;
      }
    }
    return $timeEntries;
  }

  /**
   * Get or loads projects from endpoint.
   *
   * @param array $params
   *   boolean is_active: Pass true (default) to only return active projects and false to return inactive projects.
   *   integer client_id: Only return projects belonging to the client with the given ID.
   *   datetime updated_since: Only return projects that have been updated since the given date and time.
   *   integer page: The page number to use in pagination. For instance, if you make a list request and receive 100 records, your subsequent call can include page=2 to retrieve the next page of the list. (Default: 1)
   *   integer per_page: The number of records to return per page. Can range between 1 and 100. (Default: 10)
   *
   * @return \FH\HarvestApiClient\Model\Project\Project[]
   */
  public function getProjects($page = 1) {
    $params = array_merge(['is_active' => true], ['page' => $page]);

    $page_next = $this->projectEndpoint->list($params)->getNextPage();
    if ($page_next >= $page) {
      $old_projects = $this->projects;
      $this->projects = $this->projectEndpoint->list($params)->getProjects();
      if ($old_projects != NULL) {
        $new_projects = array_merge($old_projects, $this->projects);
      } else {
        $new_projects = $this->projects;
      }
      $this->projects = $new_projects;
      $this->getProjects($page += 1);
    }
    return $this->projects;
  }

  /**
   * {@inheritdoc}
   */
  public function getProjectByCode($project_code) {
    foreach ($this->getProjects() as $project) {
      if ($project_code == $project->getCode()) {
        return $project;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getProjectById($project_id) {
    return $this->projectEndpoint->retrieve($project_id);
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
      //'updated_since' => $this->getDate(),
      'project_id' => $harvestProject->getId(),
      'is_running' => false,
      'page' => $page,
    ]);

    $timeEntries = $timeEntryCollection->getTimeEntries();

    if ($page < $timeEntryCollection->getTotalPages()) {
      $timeEntries = array_merge($timeEntries, $this->fetchTimeEntriesRecursive($harvestProject, $page += 1));
    }
    return $timeEntries;
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

}
