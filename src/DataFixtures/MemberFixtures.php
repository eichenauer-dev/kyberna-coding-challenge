<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Member;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Loads sample member data into the database for local development.
 */
class MemberFixtures extends Fixture
{
    public const MEMBER_ALICE = 'member-alice';
    public const MEMBER_BOB = 'member-bob';
    public const MEMBER_CAROL = 'member-carol';

    /**
     * Persists sample members and registers fixture references.
     *
     * @param ObjectManager $manager
     *
     * @return void
     *
     * @throws \DateMalformedStringException
     */
    public function load(ObjectManager $manager): void
    {
        $members = [
            [
                'reference' => self::MEMBER_ALICE,
                'name' => 'Alice Müller',
                'email' => 'alice.mueller@example.com',
                'membershipDate' => '2024-01-15',
            ],
            [
                'reference' => self::MEMBER_BOB,
                'name' => 'Bob Schmidt',
                'email' => 'bob.schmidt@example.com',
                'membershipDate' => '2024-06-01',
            ],
            [
                'reference' => self::MEMBER_CAROL,
                'name' => 'Carol Weber',
                'email' => 'carol.weber@example.com',
                'membershipDate' => '2025-03-10',
            ],
        ];

        foreach ($members as $data) {
            $member = (new Member())
                ->setName($data['name'])
                ->setEmail($data['email'])
                ->setMembershipDate(new \DateTimeImmutable($data['membershipDate']));

            $manager->persist($member);
            $this->addReference($data['reference'], $member);
        }

        $manager->flush();
    }
}
