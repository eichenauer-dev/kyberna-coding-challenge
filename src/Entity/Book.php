<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a book available in the library catalog.
 */
#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ORM\Table(name: 'book')]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $author = null;

    #[ORM\Column(length: 20)]
    private ?string $isbn = null;

    #[ORM\Column]
    private ?int $availableCopies = null;

    /**
     * Returns the unique identifier of the book.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Returns the title of the book.
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Sets the title of the book.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Returns the author of the book.
     *
     * @return string|null
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * Sets the author of the book.
     *
     * @param string $author
     *
     * @return $this
     */
    public function setAuthor(string $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Returns the ISBN of the book.
     *
     * @return string|null
     */
    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    /**
     * Sets the ISBN of the book.
     *
     * @param string $isbn
     *
     * @return $this
     */
    public function setIsbn(string $isbn): static
    {
        $this->isbn = $isbn;

        return $this;
    }

    /**
     * Returns the number of available copies.
     *
     * @return int|null
     */
    public function getAvailableCopies(): ?int
    {
        return $this->availableCopies;
    }

    /**
     * Sets the number of available copies.
     *
     * @param int $availableCopies
     *
     * @return $this
     */
    public function setAvailableCopies(int $availableCopies): static
    {
        $this->availableCopies = $availableCopies;

        return $this;
    }
}
