const socket = new WebSocket('ws://127.0.0.1:3030');
const messagesDiv = document.getElementById('messages');
const participantesDiv = document.getElementById('qtd-participantes');

const groupId = new URLSearchParams(window.location.search).get('group');

socket.onopen = () => {
    console.log('Conectado ao servidor WebSocket');
    messagesDiv.innerHTML += `<p>SERVIDOR: Seja bem vindo!</p>`;

    socket.send(JSON.stringify({type: 'join', groupId: groupId}));

    messagesDiv.innerHTML += `<p>SERVIDOR: Você entrou em: ${groupId}!</p>`;
};

socket.onclose = () => {
    console.log('fechado!');
}

socket.onerror = (error) => {
    console.log('Erro!', error);
}

socket.onmessage = (event) => {
    let mensagem = JSON.parse(event.data);
    console.log(`Mensagem: ${mensagem.message}`);

    switch (mensagem.type) {
        case 'message':
            messagesDiv.innerHTML += `<p>ELE: ${mensagem.message}</p>`;
            break;
        case 'updateInterface':
            console.log(participantesDiv);
            participantesDiv.innerHTML = mensagem.message;
            break;
        default:
            messagesDiv.innerHTML += `<p>DESCONHECIDO: ${mensagem.message}</p>`;
            break;
    }
};

function sendMessage() {
    const input = document.getElementById('messageInput');
    const message = input.value;

    messagesDiv.innerHTML += `<p>VOCE: ${message}</p>`;

    socket.send(JSON.stringify({type: 'message', groupId: groupId, message: message}));
    input.value = '';
}