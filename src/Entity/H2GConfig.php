<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\H2GConfigRepository")
 */
class H2GConfig
{
  /**
   * @ORM\Id()
   * @ORM\GeneratedValue()
   * @ORM\Column(type="integer")
   */
  private $id;

  /**
   * @ORM\Column(type="integer")
   */
  private $harvest_id;

  /**
   * @ORM\Column(type="integer")
   */
  private $gitlab_id;

  /**
   * Display name.
   *
   * @var string
   */
  private $harvest_display_name = '';

  /**
   * Display name.
   *
   * @var string
   */
  private $gitlab_display_name = '';

  public function getId(): ?int
  {
      return $this->id;
  }

  public function getHarvestId(): ?int
  {
      return $this->harvest_id;
  }

  public function setHarvestId(int $harvest_id): self
  {
      $this->harvest_id = $harvest_id;

      return $this;
  }

  public function getGitlabId(): ?int
  {
      return $this->gitlab_id;
  }

  public function setGitlabId(int $gitlab_id): self
  {
      $this->gitlab_id = $gitlab_id;

      return $this;
  }

  /**
   * @return string
   */
  public function getHarvestDisplayName(): string
  {
    return $this->harvest_display_name;
  }

  /**
   * @param string $harvest_display_name
   */
  public function setHarvestDisplayName(string $harvest_display_name): void
  {
    $this->harvest_display_name = $harvest_display_name;
  }

  /**
   * @return string
   */
  public function getGitlabDisplayName(): string
  {
    return $this->gitlab_display_name;
  }

  /**
   * @param string $gitlab_display_name
   */
  public function setGitlabDisplayName(string $gitlab_display_name): void
  {
    $this->gitlab_display_name = $gitlab_display_name;
  }

}
