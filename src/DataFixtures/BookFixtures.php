<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BookFixtures extends Fixture
{
    public const BOOK_GATSBY = 'book-gatsby';
    public const BOOK_1984 = 'book-1984';
    public const BOOK_HOBBIT = 'book-hobbit';
    public const BOOK_DUNE = 'book-dune';
    public const BOOK_PRIDE = 'book-pride';

    public function load(ObjectManager $manager): void
    {
        $books = [
            [
                'reference' => self::BOOK_GATSBY,
                'title' => 'The Great Gatsby',
                'author' => 'F. Scott Fitzgerald',
                'isbn' => '9780743273565',
                'availableCopies' => 3,
            ],
            [
                'reference' => self::BOOK_1984,
                'title' => '1984',
                'author' => 'George Orwell',
                'isbn' => '9780451524935',
                'availableCopies' => 2,
            ],
            [
                'reference' => self::BOOK_HOBBIT,
                'title' => 'The Hobbit',
                'author' => 'J.R.R. Tolkien',
                'isbn' => '9780547928227',
                'availableCopies' => 4,
            ],
            [
                'reference' => self::BOOK_DUNE,
                'title' => 'Dune',
                'author' => 'Frank Herbert',
                'isbn' => '9780441172719',
                'availableCopies' => 1,
            ],
            [
                'reference' => self::BOOK_PRIDE,
                'title' => 'Pride and Prejudice',
                'author' => 'Jane Austen',
                'isbn' => '9780141439518',
                'availableCopies' => 2,
            ],
        ];

        foreach ($books as $data) {
            $book = (new Book())
                ->setTitle($data['title'])
                ->setAuthor($data['author'])
                ->setIsbn($data['isbn'])
                ->setAvailableCopies($data['availableCopies']);

            $manager->persist($book);
            $this->addReference($data['reference'], $book);
        }

        $manager->flush();
    }
}
