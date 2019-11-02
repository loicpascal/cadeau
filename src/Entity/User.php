<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="app_user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $username;

    /**
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $firstname;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthdate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Idee", mappedBy="user")
     */
    private $idees;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $role;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Idee", mappedBy="user_taking_id")
     */
    private $idees_taken;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="user", orphanRemoval=true)
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Idee", mappedBy="user_adding")
     */
    private $idees_adding;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Team", inversedBy="users")
     */
    private $teams;

    public function __construct()
    {
        $this->isActive = true;
        $this->idees = new ArrayCollection();
        $this->idees_taken = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->idees_adding = new ArrayCollection();
        $this->teams = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function getFullname(): ?string
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getSalt()
    {
        return null;
    }

    public function getRoles()
    {
        return array($this->role);
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function eraseCredentials()
    {
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt
            ) = unserialize($serialized, ['allowed_classes' => false]);
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
            $idee->setUser($this);
        }

        return $this;
    }

    public function removeIdee(Idee $idee): self
    {
        if ($this->idees->contains($idee)) {
            $this->idees->removeElement($idee);
            // set the owning side to null (unless already changed)
            if ($idee->getUser() === $this) {
                $idee->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Idee[]
     */
    public function getIdeesTaken(): Collection
    {
        return $this->idees_taken;
    }

    public function addIdeesTaken(Idee $ideesTaken): self
    {
        if (!$this->idees_taken->contains($ideesTaken)) {
            $this->idees_taken[] = $ideesTaken;
            $ideesTaken->setUserTakingId($this);
        }

        return $this;
    }

    public function removeIdeesTaken(Idee $ideesTaken): self
    {
        if ($this->idees_taken->contains($ideesTaken)) {
            $this->idees_taken->removeElement($ideesTaken);
            // set the owning side to null (unless already changed)
            if ($ideesTaken->getUserTakingId() === $this) {
                $ideesTaken->setUserTakingId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Idee[]
     */
    public function getIdeesAdding(): Collection
    {
        return $this->idees_adding;
    }

    public function addIdeesAdding(Idee $ideesAdding): self
    {
        if (!$this->idees_adding->contains($ideesAdding)) {
            $this->idees_adding[] = $ideesAdding;
            $ideesAdding->setUserAdding($this);
        }

        return $this;
    }

    public function removeIdeesAdding(Idee $ideesAdding): self
    {
        if ($this->idees_adding->contains($ideesAdding)) {
            $this->idees_adding->removeElement($ideesAdding);
            // set the owning side to null (unless already changed)
            if ($ideesAdding->getUserAdding() === $this) {
                $ideesAdding->setUserAdding(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Team[]
     */
    public function getTeam(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): self
    {
        if (!$this->teams->contains($team)) {
            $this->teams[] = $team;
        }

        return $this;
    }

    public function removeTeam(Team $team): self
    {
        if ($this->teams->contains($team)) {
            $this->teams->removeElement($team);
        }

        return $this;
    }
}
