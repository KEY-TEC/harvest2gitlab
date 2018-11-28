<?php

namespace App;

use FH\HarvestApiClient\Endpoint\TimeEntryEndpoint;
use FH\HarvestApiClient\Endpoint\ProjectEndpoint;
use FH\HarvestApiClient\Client\ClientFactory;
use FH\HarvestApiClient\Model\Project\Project;
use FH\HarvestApiClient\Model\TimeEntry\TimeEntryCollection;
use JMS\Serializer\SerializerBuilder;

class Harvest2GitlabImportService implements GitlabImportServiceInterface {

  /**
   * @var \FH\HarvestApiClient\Endpoint\ProjectEndpoint
   */
  private $project;

  /**
   * @var \FH\HarvestApiClient\Endpoint\TimeEntryEndpoint
   */
  private $timeEntry;

  public function __construct($harvestAccountId, $harvestAccessToken) {
    $clientConfiguration = [
      'account_id' => $harvestAccountId,
      'access_token' => $harvestAccessToken,
      'user_agent' => 'Harvest2Gitlab Importer (info@key-tec.de)',
    ];
    $client = ClientFactory::create([], NULL, NULL, $clientConfiguration);
    $serializer = SerializerBuilder::create()
      ->addMetadataDir(
        __DIR__ . '/../vendor/freshheads/harvest-api-client/src/Model/configuration'
      )->build();
    $this->project = new ProjectEndpoint($client, $serializer);
    $this->timeEntry = new TimeEntryEndpoint($client, $serializer);
  }

  /**
   * @inheritdoc
   */
  public function importTimeEntries($projectPathWithNamespace) {
    $harvestProject = $this->getHarvestProjectByCode($projectPathWithNamespace);
    $projectTimeEntries = $this->fetchTimeEntriesRecursive($harvestProject);
    $filteredTimeEntries = $this->getTimeEntriesWithReference($harvestProject, $projectTimeEntries);
    $groupedTimeEntries = $this->
    $filteredHours = $this->getTimeEntriesHours($filteredTimeEntries);
    $allHours = $this->getTimeEntriesHours($projectTimeEntries);
  }

  /**
   * Return a project object by its code.
   *
   * @param string $project_code
   *   The project code string.
   * @return \FH\HarvestApiClient\Model\Project\Project|null
   *   The project model or NULL.
   */
  private function getHarvestProjectByCode($project_code) {
    foreach ($this->project->list()->getProjects() as $project) {
      if ($project_code == $project->getCode()) {
        return $project;
      }
    }
  }

  /**
   * Returns a list of time entries by project
   *
   * @param \FH\HarvestApiClient\Model\Project\Project $harvestProject
   *   The harvest project.
   * @param array[\FH\HarvestApiClient\Model\TimeEntry\TimeEntry] $timeEntries
   *   List of time entries to filter for. Leave empty to load items from the harvest project.
   * @return array[\FH\HarvestApiClient\Model\TimeEntry\TimeEntry]
   *   Filtered list of time entries with reference.
   * @throws \Exception
   */
  private function getTimeEntriesWithReference(Project $harvestProject, array $timeEntries = []) {
    if(empty($timeEntries)) {
      $timeEntries = $this->fetchTimeEntriesRecursive($harvestProject);
    }
    $result = [];
    foreach ($timeEntries as $timeEntry) {
      /* @var \FH\HarvestApiClient\Model\TimeEntry\TimeEntry $timeEntry */
      $external_reference = $timeEntry->getExternalReference();
      if (strpos($external_reference["id"], $harvestProject->getCode()) !== FALSE) {
        $result[] = $timeEntry;
      }
    }
    return $result;
  }

  /**
   * Group time entries by their reference id.
   *
   * @param \FH\HarvestApiClient\Model\Project\Project $harvestProject
   *   The harvest project.
   * @param array[\FH\HarvestApiClient\Model\TimeEntry\TimeEntry] $timeEntries
   *   List of time entries.
   * @return array[group => \FH\HarvestApiClient\Model\TimeEntry\TimeEntry]
   *   Grouped list of time entries by reference.
   */
  private function groupTimeEntriesByReference(Project $harvestProject, array $timeEntries) {
    $output = [];
    foreach ($timeEntries as $timeEntry) {
      /* @var \FH\HarvestApiClient\Model\TimeEntry\TimeEntry $timeEntry */
      if (!empty($timeEntry->getExternalReference())) {
        $output[str_] = $timeEntry;
      }
    }
    return $output;
  }

  /**
   * Calculates the hours of all time entries from the given list.
   *
   * @param array[\FH\HarvestApiClient\Model\TimeEntry\TimeEntry] $timeEntries
   *   List of time entries.
   * @return int
   *   Sum hours.
   */
  private function getTimeEntriesHours(array $timeEntries) {
    $sum = 0;
    foreach ($timeEntries as $timeEntry) {
      $sum += $timeEntry->getHours();
    }
    return $sum;
  }

  /**
   * Fetches all time entries of a project.
   * The Api returns up to 100 results, so we call this in a recursion to get trough all pages.
   *
   * @param \FH\HarvestApiClient\Model\Project\Project $harvestProject
   *   The harvest project.
   * @param int $page
   *   Pager for API.
   * @return array[\FH\HarvestApiClient\Model\TimeEntry\TimeEntry]
   *   List of all time entries of the project.
   * @throws \Exception
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

  /**
   * Returns the date string from 30 days ago.
   *
   * @return false|string
   */
  private function getDate() {
    $timestamp = strtotime('-30 days');
    return date("c", $timestamp);
  }

}
