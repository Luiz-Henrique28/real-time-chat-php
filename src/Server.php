<?php
    require __DIR__ . '/../vendor/autoload.php';

    use Ratchet\MessageComponentInterface;
    use Ratchet\ConnectionInterface;

    class ChatServer implements MessageComponentInterface {
        protected $clients;
    
        public function __construct() {
            $this->clients = new \SplObjectStorage;
        }
    
        public function onOpen(ConnectionInterface $conn) {
            $this->clients->attach($conn);

//            $conn->send(json_encode([
//                'type' => 'pessoal',
//                'message' => 'seja bem vindo!!!!'
//            ]));

            foreach ($this->clients as $client) {
                $client->send(json_encode([
                    'type' => 'updateInterface',
                    'message' => ($this->clients->count()),
                ]));
            }

            echo "Nova conexão! ({$conn->resourceId})\n";
            echo "Participantes: {$this->clients->count()}\n";
        }
    
        public function onMessage(ConnectionInterface $from, $msg) {
            echo "Received message: {$msg}\n";
            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    echo "Enviando mensagem para o cliente ({$client->resourceId})\n";
                    $client->send(json_encode([
                        'type' => 'chat',
                        'message' => $msg,
                    ]));
                }
            }
        }
    
        public function onClose(ConnectionInterface $conn) {
            $this->clients->detach($conn);

            echo "Conexão encerrada! ({$conn->resourceId})\n";
            echo "Participantes: {$this->clients->count()}\n";


            foreach ($this->clients as $client) {
                $client->send(json_encode([
                    'type' => 'updateInterface',
                    'message' => ($this->clients->count() + 1),
                ]));
            }
        }
    
        public function onError(ConnectionInterface $conn, \Exception $e) {
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