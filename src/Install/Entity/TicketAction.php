<?php

declare(strict_types=1);

namespace App\Install\Entity;

use App\Entity\TicketAction as TicketActionEntity;
use Doctrine\ORM\EntityManagerInterface;

final class TicketAction implements InstallerEntityInterface
{
    private array $actions = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function initialize(): void
    {
        $this->actions = [
            ['description' => 'Create a ticket', 'friendlyName' => 'Create', 'fullName' => 'Create', 'triggerAction' => 'ticket.create', 'rolesNeeded' => 'ROLE_USER'],
            ['description' => 'Comment on a ticket', 'friendlyName' => 'Comment', 'fullName' => 'Comment', 'triggerAction' => 'ticket.comment', 'rolesNeeded' => 'ROLE_USER'],
            ['description' => 'Assign a user to a ticket', 'friendlyName' => 'Assign User', 'fullName' => 'Assign User', 'triggerAction' => 'ticket.assign_user', 'rolesNeeded' => 'ROLE_TECHNICIAN'],
            ['description' => 'Assign a group to a ticket', 'friendlyName' => 'Assign Group', 'fullName' => 'Assign Group', 'triggerAction' => 'ticket.assign_group', 'rolesNeeded' => 'ROLE_TECHNICIAN'],
            ['description' => 'Assign a status to a ticket', 'friendlyName' => 'Assign Status', 'fullName' => 'Assign Status', 'triggerAction' => 'ticket.assign_status', 'rolesNeeded' => 'ROLE_TECHNICIAN'],
            ['description' => 'Modify info on a ticket', 'friendlyName' => 'Modify Info', 'fullName' => 'Modify Info', 'triggerAction' => 'ticket.modify_info', 'rolesNeeded' => 'User'],
        ];

        foreach ($this->actions as $action) {
            $ticketAction = new TicketActionEntity();

            $ticketAction->setDescription($action['description'])
                ->setFriendlyName($action['friendlyName'])
                ->setFullName($action['fullName'])
                ->setEnabled(true)
                ->setTriggerAction($action['triggerAction'])
                ->setRolesNeeded([$action['rolesNeeded']])
            ;

            $this->entityManager->persist($ticketAction);
        }

        $this->entityManager->flush();
    }

    public function verify(): bool
    {
        $ticketActions = $this->entityManager->getRepository(TicketActionEntity::class)->findAll();

        $checkActions = [];
        $errorCount = 0;

        foreach ($this->actions as $action) {
            $checkActions[] = $action['fullName'];
        }

        foreach ($ticketActions as $ticketAction) {
            if (!in_array($ticketAction->getFullName(), $checkActions)) {
                ++$errorCount;
            }
        }

        return 0 === $errorCount;
    }
}
