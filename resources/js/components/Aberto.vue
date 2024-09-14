<script>
import { mapState, mapActions } from 'vuex';
import axios from 'axios';
import Echo from "laravel-echo";
import Pusher from "pusher-js";

// Configuração do Pusher e Echo
window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY, // Use import.meta.env para acessar variáveis de ambiente no Vite
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER, // O cluster
    wsHost: import.meta.env.VITE_PUSHER_HOST || '127.0.0.1',
    wsPort: import.meta.env.VITE_PUSHER_PORT || 6001,
    forceTLS: import.meta.env.VITE_PUSHER_SCHEME === 'https',
    disableStats: true,
    enabledTransports: ['ws'], // Use WebSocket
});


export default {
    data() {
        return {
            mensagens: [],
            novaMensagem: '',
            chatSelecionado: null,
            loading: false,
            loadingstats: 0,
        }

    },
    computed: {
        ...mapState(['chatsAbertos', 'user']),
        idServidor() {
            return window.idServidor;
        }
    },
    methods: {
        ...mapActions(['fetchChatsAbertos']),
        async getMessage(chat_id) {
            this.loadingstats = 1;
            try {
                const response = await axios.get('/api/mensagem', {
                    params: { chat_id: chat_id },
                });
                this.mensagens = response.data;
                this.chatSelecionado = chat_id;

                // Reconfigura o canal para o chat selecionado
                window.Echo.channel('chat.' + chat_id)
                    .listen('MensagemEnviada', (event) => {
                        console.log('Mensagem recebida:', event.mensagem);
                        this.mensagens.push(event.mensagem);
                    });
            } catch (error) {
                console.error('Erro ao buscar mensagens:', error);
            }
        },
        async mandarMensagem() {
            if (!this.chatSelecionado) {
                console.error('Nenhum chat selecionado.');
                return;
            }

            this.loading = true;

            try {
                const response = await axios.post('/api/mensagem/enviarMensagem', {
                    admin_id: parseInt(idServidor, 10),
                    chat_id: this.chatSelecionado,
                    mensagem: this.novaMensagem,
                });
                console.log('Mensagem enviada:', response.data);
                this.novaMensagem = '';
                await this.getMessage(this.chatSelecionado);
            } catch (error) {
                console.error('Erro ao enviar mensagem:', error);
            } finally {
                this.loading = false;
            }
        },
        async fecharChat() {
            if (!this.chatSelecionado) {
                console.error('Nenhum chat selecionado.');
                return;
            }

            try {
                const response = await axios.post('/api/chat/fecharChat', {
                    chat_id: this.chatSelecionado,
                },
                );
                console.log('Chat fechado:', response.data);
                this.chatSelecionado = null;
                this.mensagens = [];
                this.loadingstats = 0;
            } catch (error) {
                console.error('Erro ao fechar chat:', error);
            }
        },
    },
    mounted() {


        this.fetchChatsAbertos();
        console.log('chegou aq');
        const tokenCSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        console.log('Token CSRF:', tokenCSRF);
        window.Echo.channel('chat.' + this.chatSelecionado)
            .listen('MensagemEnviada', (event) => {
                console.log('Mensagem recebida:', event.mensagem);
                this.mensagens.push(event.mensagem);
            });

        // Disparar o evento de teste assim que o componente é montado
        axios.post('/api/teste-conexao', {
            mensagem: 'Conexão estabelecida em ' + (import.meta.env.VITE_APP_NAME || 'Desconhecido')
        }).then(response => {
            console.log('Evento de conexão enviado:', response.data);
        }).catch(error => {
            console.error('Erro ao enviar evento de conexão:', error);
        });

        // Listener para o evento TesteDeConexao
        window.Echo.channel('public')
            .listen('.TesteDeConexao', (event) => {
                console.log('Mensagem recebida:', event.mensagem);
            });
    },
};
</script>

<template>
    <h1 style="margin-right:2.5%; margin-left:5%; margin-top:2.5%" class="lemon-font">Chats abertos</h1>
    <div class="d-flex Box">
        <!-- Lista de Conversas -->
        <div class="col-4" style=" height: 100%; overflow-y: auto; background-color: rgba(17, 132, 174, 0.1); border-top-left-radius: 2.5rem; border-bottom-left-radius: 1.5rem;">
            <div>
                <div v-for="chat in chatsAbertos" :key="chat.id" @click="getMessage(chat.id)" class="card"
                    style="background-color: rgba(100, 100, 100, 0); width: 100%;">
                    <div class="card-body">
                        <h3 class="card-title">{{ chat.assunto }}</h3>
                        <p class="card-text">
                            <strong>Criado em:</strong> {{ chat.criado_em }}
                        </p>
                        <p v-if="chat.linha != null" class="card-text">
                            <strong>Linha:</strong> {{ chat.linha }}
                        </p>
                        <p class="card-text">
                            <strong>Status:</strong> {{ chat.chat_status }}
                        </p>
                        <hr>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensagens do Chat -->
        <div v-if="mensagens.length" class="col-8 d-flex flex-column p-3"
            style=" width: calc(66.66%); background-color: rgba(0, 0, 0, 0.7);">
            <div class="border-bottom pb-3 mb-3 d-flex flex-row justify-content-between">
                <h3 class="text-white">Chat ativo</h3>
                <button @click="fecharChat()" class="btn btn-danger btn-sm" :disabled="loading">Encerrar chat</button>
            </div>

            <div class="d-flex flex-column justify-content-between" style="height: 90%;">
                <div class="chat-messages flex-grow-1 overflow-auto d-flex flex-column mb-3">
                    <div v-for="mensagem in mensagens" :key="mensagem.id" class="message-container">
                        <div class="alert"
                            :class="{ 'alert-secondary': mensagem.chat_id % 2 === 0, 'alert-dark': mensagem.chat_id % 2 !== 0 }"
                            style="color: white;">
                            <span>{{ mensagem.mensagem }}</span>
                            <div class="text-muted small">Chat ID: {{ mensagem.chat_id }}</div>
                        </div>
                    </div>
                </div>

                <!-- Input para nova mensagem -->
                <div class="d-flex flex-row justify-content-between" style="width: 100%;">
                    <input class="form-control me-2 my-input" type="text" maxlength="255" v-model="novaMensagem"
                        placeholder="Digite sua mensagem">
                    <button @click="mandarMensagem()" class="btn btn-success col-2" :disabled="loading">Enviar
                        mensagem</button>
                </div>
            </div>
        </div>

        <!-- Placeholder quando nenhum chat está selecionado -->
        <div v-else class="col-8 d-flex align-items-center justify-content-center"
            style="height: calc(100vh - 3.5rem);">
            <h1 v-if="this.loadingstats == 0" class="text-white">Acesse um chat para visualizá-lo.</h1>
            <div v-if="this.loadingstats == 1" class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</template>
