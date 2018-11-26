<?php

namespace App\Controller;

use Gitlab\Client;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Updates\SiteUpdateManager;


class GitlabController extends AbstractController {

  public function getClient($git_url, $git_token) {
    $client = Client::create($git_url)->authenticate($git_token, Client::AUTH_URL_TOKEN);
    return $client;
  }
  /**
   * @Route("/")
   */
  public function projects($git_url,$git_token) {
    $client = $this->getClient($git_url,$git_token);
    $projects = $client->projects()->all();
    return $this->render('/projects.html.twig', [
        'projects' => $projects,
    ]);
  }
  /**
   * @Route("/project/{project_id}/milestones", name="milestones")
   */
  public function milestones($project_id, $git_url,$git_token) {
    $client = $this->getClient($git_url,$git_token);
    $milestones = $client->milestones()->all($project_id);
    $current_project = $client->projects()->show($project_id);

    return $this->render('/milestones.html.twig', [
        'milestones' => $milestones,
        'current_project' => $current_project,
        'project_id' => $project_id
    ]);
  }

  /**
   * @Route("/project/{project_id}/milestones/{milestone_id}/issues", name="issues")
   */
  public function issues($milestone_id, $project_id, $git_url,$git_token) {
    $client = $this->getClient($git_url,$git_token);
    $milestone_issues = $client->milestones()->issues($project_id, $milestone_id);
    $current_milestone = $client->milestones()->show($project_id, $milestone_id);
    $total_estimate_time = 0;
    $total_spent_time = 0;
    foreach ($milestone_issues as $milestone_issue) {
      if (count($milestone_issues) >= 1) {
        $total_estimate_time += $milestone_issue['time_stats']['time_estimate'];
      }
      if (count($milestone_issues) >= 1) {
        $total_spent_time += $milestone_issue['time_stats']['total_time_spent'];
      }

    }
    return $this->render('/issues.html.twig', [
        'milestone_issues' => $milestone_issues,
        'current_milestone' => $current_milestone,
        'total_estimate_time' => gmdate("H:i",$total_estimate_time),
        'total_spent_time' => gmdate("H:i",$total_spent_time),
        'project_id' => $project_id,
        'milestone_id' => $milestone_id
    ]);
  }

  public function new(SiteUpdateManager $siteUpdateManager)
  {
    // ...

    if ($siteUpdateManager->notifyOfSiteUpdate()) {
      $this->addFlash('success', 'Notification mail was sent successfully.');
    }

    // ...
  }
}