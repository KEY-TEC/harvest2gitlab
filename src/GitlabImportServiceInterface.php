<?php

namespace App;

interface GitlabImportServiceInterface {

  /**
   * Import time entries of a given project.
   *
   * @param string $project_path_with_namespace
   * The project code
   *
   * @return mixed
   */
  public function importTimeEntries($project_path_with_namespace);

}
