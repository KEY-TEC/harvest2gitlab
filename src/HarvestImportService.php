<?php

namespace App;

// Use the composer autoloader to load dependencies

use FH\HarvestApiClient\Endpoint\TimeEntryEndpoint;
use FH\HarvestApiClient\Endpoint\ProjectEndpoint;
use FH\HarvestApiClient\Client\ClientFactory;
use JMS\Serializer\SerializerBuilder;

class HarvestImportService implements HarvestImportServiceInterface {

  /**
   * @var \FH\HarvestApiClient\Endpoint\ProjectEndpoint
   */
  private $project;

  /**
   * @var \FH\HarvestApiClient\Endpoint\TimeEntryEndpoint
   */
  private $timeEntry;

  function __construct($harvest_account_id, $harvest_access_token) {
    $clientConfiguration = [
      'account_id' => $harvest_account_id,
      'access_token' => $harvest_access_token,
      'user_agent' => 'Harvest2Gitlab Importer (info@key-tec.de)',
    ];
    $client = ClientFactory::create([], NULL, NULL, $clientConfiguration);
    $serializer = SerializerBuilder::create()
      ->addMetadataDir(

        '/var/www/vendor/freshheads/harvest-api-client/src/Model/configuration'
      )
      ->build();
    $this->project = new ProjectEndpoint($client, $serializer);
    $this->timeEntry = new TimeEntryEndpoint($client, $serializer);
  }

  function getDate() {
    $timestamp = strtotime('-30 days');
    $date = date("c", $timestamp);
    return $date;
  }

  function getId($project_id) {
    foreach ($this->project->list()->getProjects() as $project_items) {
      $project_result = $project_items->getCode();
      if ($project_id == $project_result) {
        $id = $project_items->getId();
        return $id;
      }
    }
  }

  function fetchAllTimeEntries($project_id, $page) {
    $fetchedTimeEntries = $this->timeEntry->findPaged(
      $page, ['updated_since' => $this->getDate(), "project_id" => $this->getId($project_id)]);
    if (!empty($this->timeEntry->findPaged($page, ['updated_since' => $this->getDate(), "project_id" => $this->getId($project_id)]))) {
      $fetchedTimeEntries = array_merge(
        $fetchedTimeEntries, $this->fetchAllTimeEntries($project_id, $page += 1));
    }
    return $fetchedTimeEntries;
  }

  function getTimeEntries($project_id, $ids) {

    $timeEntries = $this->timeEntry->findPaged(1, ['updated_since' => $this->getDate(), "project_id" => $this->getId($project_id)]);
    $result = [];
    foreach ($timeEntries as $timeEntry) {
        $external_reference = $timeEntry->getExternalReference();
      if ($external_reference != NULL) {
        $external_id = $timeEntry->getExternalReference()['id'];
        // First we check if the timeentry is one we are looking for.
        if (in_array($external_id, $ids)) {
          // Second we sum the values up.
          if (!isset($result[$external_id])) {
            $result[$external_id] = $timeEntry->getHours();
          }
          else {
            $result[$external_id] += $timeEntry->getHours();
          }
        }
      }
    }
    return $result;
  }

}
