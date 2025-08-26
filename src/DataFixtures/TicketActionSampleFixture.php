<?php

namespace App\DataFixtures;

use App\Entity\TicketAction as TicketActionEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TicketActionSampleFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $actions = [
            ['description' => 'Create a ticket', 'friendlyName' => 'Create', 'fullName' => 'Create', 'triggerAction' => 'ticket.create', 'rolesNeeded' => 'ROLE_USER'],
            ['description' => 'Comment on a ticket', 'friendlyName' => 'Comment', 'fullName' => 'Comment', 'triggerAction' => 'ticket.comment', 'rolesNeeded' => 'ROLE_USER'],
            ['description' => 'Assign a user to a ticket', 'friendlyName' => 'Assign User', 'fullName' => 'Assign User', 'triggerAction' => 'ticket.assign_user', 'rolesNeeded' => 'ROLE_TECHNICIAN'],
            ['description' => 'Assign a group to a ticket', 'friendlyName' => 'Assign Group', 'fullName' => 'Assign Group', 'triggerAction' => 'ticket.assign_group', 'rolesNeeded' => 'ROLE_TECHNICIAN'],
            ['description' => 'Assign a status to a ticket', 'friendlyName' => 'Assign Status', 'fullName' => 'Assign Status', 'triggerAction' => 'ticket.assign_status', 'rolesNeeded' => 'ROLE_TECHNICIAN'],
            ['description' => 'Modify info on a ticket', 'friendlyName' => 'Modify Info', 'fullName' => 'Modify Info', 'triggerAction' => 'ticket.modify_info', 'rolesNeeded' => 'User'],
        ];

        foreach ($actions as $action) {
            $ticketAction = new TicketActionEntity();

            $ticketAction
                ->setDescription($action['description'])
                ->setFriendlyName($action['friendlyName'])
                ->setFullName($action['fullName'])
                ->setEnabled(true)
                ->setTriggerAction($action['triggerAction'])
                ->setRolesNeeded([$action['rolesNeeded']])
            ;

            $manager->persist($ticketAction);
        }

        $manager->flush();
    }
}
