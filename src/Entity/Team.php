<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TeamRepository")
 */
class Team
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=6)
     */
    private $code;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="teams")
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $leader;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Idee", mappedBy="team")
     */
    private $idees;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->idees = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addTeam($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeTeam($this);
        }

        return $this;
    }

    public function getLeader(): ?User
    {
        return $this->leader;
    }

    public function setLeader(?User $leader): self
    {
        $this->leader = $leader;

        return $this;
    }

    /**
     * @return Collection|Idee[]
     */
    public function getIdees(): Collection
    {
        return $this->idees;
    }

    public function addIdee(Idee $idee): self
    {
        if (!$this->idees->contains($idee)) {
            $this->idees[] = $idee;
            $idee->addTeam($this);
        }

        return $this;
    }

    public function removeIdee(Idee $idee): self
    {
        if ($this->idees->contains($idee)) {
            $this->idees->removeElement($idee);
            $idee->removeTeam($this);
        }

        return $this;
    }
}
