<?php
require __DIR__ . '/../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatServer implements MessageComponentInterface
{
    protected $clients;
    protected $groups;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->groups = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "Nova conexão! ({$conn->resourceId})\n";
        echo "Participantes: {$this->clients->count()}\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        echo "Received message: {$msg}\n";

        $data = json_decode($msg);

        switch ($data->type) {
            case 'join':
                $groupId = $data->groupId;
                if (!isset($this->groups[$groupId])) {
                    $this->groups[$groupId] = new \SplObjectStorage;
                }

                if (!$this->groups[$groupId]->contains($from)) {
                    $this->groups[$groupId]->attach($from);
                }

                foreach ($this->groups[$groupId] as $client) {

                    $client->send(json_encode([
                        'type' => 'updateInterface',
                        'message' => $this->groups[$groupId]->count(),
                    ]));

                    if ($client !== $from) {

                        $client->send(json_encode([
                            'type' => 'globalMessage',
                            'message' => "Usuário {$from->resourceId} entrou no grupo.",
                        ]));

                    }
                }
                break;

            case 'message':
                foreach ($this->groups[$data->groupId] as $client) {
                    if ($from !== $client) {
                        $client->send(json_encode([
                            'type' => 'message',
                            'from' => $from->resourceId,
                            'message' => $data->message,
                        ]));
                    }
                }
                break;
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        foreach ($this->groups as $groupId => $clients) {
            if ($clients->contains($conn)) {
                $clients->detach($conn);
                echo "Usuário {$conn->resourceId} removido do grupo $groupId\n";

                foreach ($clients as $client) {
                    $client->send(json_encode([
                        'type' => 'updateInterface',
                        'message' => $this->groups[$groupId]->count(),
                    ]));
                }
            }

            if (count($clients) === 0) {
                unset($this->groups[$groupId]);
                echo "Grupo $groupId removido (vazio).\n";
            }
        }

        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Erro: {$e->getMessage()}\n";
        $conn->close();
    }
}

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

// todo: tentar fazer rodar esse servidor externo ao docker automaticamente!!!
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ChatServer()
        )
    ),
    3030
);

echo "Servidor WebSocket rodando na porta 3030...\n";
$server->run();