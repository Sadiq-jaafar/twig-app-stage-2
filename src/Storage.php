<?php
namespace App;

class Storage {
    private string $dataDir;

    public function __construct() {
        $this->dataDir = __DIR__ . '/../data';
        if (!file_exists($this->dataDir)) {
            mkdir($this->dataDir, 0777, true);
        }
    }

    private function getTicketsFile(string $userId): string {
        return $this->dataDir . "/tickets_{$userId}.json";
    }

    private function ensureTicketsFile(string $userId): void {
        $file = $this->getTicketsFile($userId);
        if (!file_exists($file)) {
            file_put_contents($file, '[]');
        }
    }

    public function loadTickets(string $userId): array {
        $this->ensureTicketsFile($userId);
        return json_decode(file_get_contents($this->getTicketsFile($userId)), true) ?? [];
    }

    public function saveTickets(string $userId, array $tickets): void {
        file_put_contents($this->getTicketsFile($userId), json_encode($tickets, JSON_PRETTY_PRINT));
    }

    public function addTicket(string $userId, array $ticket): array {
        $tickets = $this->loadTickets($userId);
        
        $ticket['id'] = 't_' . bin2hex(random_bytes(8));
        $ticket['userId'] = $userId;
        $ticket['createdAt'] = time();
        
        $tickets[] = $ticket;
        $this->saveTickets($userId, $tickets);
        
        return $ticket;
    }

    public function updateTicket(string $userId, string $ticketId, array $updates): array {
        $tickets = $this->loadTickets($userId);
        
        $index = array_search($ticketId, array_column($tickets, 'id'));
        if ($index === false) {
            throw new \Exception('Ticket not found');
        }
        
        $ticket = $tickets[$index];
        $ticket = array_merge($ticket, $updates);
        $tickets[$index] = $ticket;
        
        $this->saveTickets($userId, $tickets);
        return $ticket;
    }

    public function deleteTicket(string $userId, string $ticketId): void {
        $tickets = $this->loadTickets($userId);
        $tickets = array_filter($tickets, fn($t) => $t['id'] !== $ticketId);
        $this->saveTickets($userId, array_values($tickets));
    }

    public function getTicketStats(string $userId): array {
        $tickets = $this->loadTickets($userId);
        
        $total = count($tickets);
        $open = count(array_filter($tickets, fn($t) => $t['status'] === 'open'));
        $in_progress = count(array_filter($tickets, fn($t) => $t['status'] === 'in_progress'));
        $closed = count(array_filter($tickets, fn($t) => $t['status'] === 'closed'));
        
        return [
            'total' => $total,
            'open' => $open,
            'in_progress' => $in_progress,
            'closed' => $closed
        ];
    }
}