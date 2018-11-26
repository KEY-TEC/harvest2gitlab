<?php
namespace App;
interface HarvestImportServiceInterface {

  public function getTimeEntries($project_id, $ids);
}