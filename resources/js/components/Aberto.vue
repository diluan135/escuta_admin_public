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

            const confirmation = window.confirm('Tem certeza de que deseja encerrar este chat?');
            if (!confirmation) {
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
                this.fetchChatsAbertos();
            } catch (error) {
                console.error('Erro ao fechar chat:', error);
            }
        },
        formatarData(data) {
            const date = new Date(data);

            // Formatando a hora
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const time = `${hours}:${minutes}`;

            // Formatando a data
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Meses começam do 0
            const year = date.getFullYear();
            const formattedDate = `${day}/${month}/${year}`;

            return `${time}, em ${formattedDate}`;
        }
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
        <div class="col-4"
            style=" height: 100%; overflow-y: auto; background-color: rgba(17, 132, 174, 0.1); border-top-left-radius: 2.5rem; border-bottom-left-radius: 1.5rem;">
            <div>
                <div v-for="chat in chatsAbertos" :key="chat.id" @click="getMessage(chat.id)" class="card"
                    style="background-color: rgba(100, 100, 100, 0); width: 100%;">
                    <div class="card-body" style="padding-left: 2rem;">
                        <h3 class="card-title">{{ chat.assunto }}</h3>
                        <p class="card-text">
                            <strong>Criado em:</strong> {{ chat.criado_em }}
                        </p>
                        <p v-if="chat.linha != null" class="card-text">
                            <strong>Linha:</strong> {{ chat.linha }}
                        </p>
                        <p class="card-text mb-4">
                            <strong>Status:</strong> {{ chat.chat_status }}
                        </p>
                        <hr style="margin: 0;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensagens do Chat -->
        <div v-if="mensagens.length" class="col-8 d-flex flex-column p-3"
            style=" width: calc(66.66%); background-color: rgba(0, 0, 0, 0.7);">
            <div class="border-bottom pb-3 mb-3 d-flex flex-row justify-content-between">
                <h3 class="text-white">Chat ativo</h3>
                <button @click="fecharChat()" class="btn btn-danger btn-sm" :disabled="loading">Fechar chat</button>
            </div>

            <div class="d-flex flex-column justify-content-between" style="height: 90%;">
                <div class="chat-messages flex-grow-1 overflow-auto d-flex flex-column mb-3">
                    <div v-for="mensagem in mensagens" :key="mensagem.id"
                        :class="{ 'd-flex justify-content-start': mensagem.admin_id !== null, 'd-flex justify-content-end': mensagem.admin_id === null }"
                        style="margin-bottom: 0.5rem;">
                        <div class="alert alert-dark"
                            style="color: white; display: inline-block; max-width: 70%; word-wrap: break-word; border: none;">
                            <div class="message-content">
                                <!-- Rótulo acima da mensagem -->
                                <div style="color: #6adae9 ; font-weight: bold;">
                                    {{ mensagem.admin_id !== null ? 'Admin:' : 'Você:' }}
                                </div>
                                <span>{{ mensagem.mensagem }}</span>
                                <!-- Horário no canto inferior direito -->
                                <div style="display: block; text-align: right; color: #AAAAAA; font-size: 12px;">
                                    às {{ formatarData(mensagem.enviado_em) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Input para nova mensagem -->
                <div class="d-flex flex-row justify-content-between" style="width: 100%;">
                    <input class="form-control me-2 my-input" type="text" maxlength="255" v-model="novaMensagem"
                        placeholder="Digite sua mensagem">
                    <button @click="mandarMensagem()" class="btn btn-success col-1" :disabled="loading">
                        <i class="ph ph-paper-plane-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Placeholder quando nenhum chat está selecionado -->
        <div v-else class="col-8 d-flex align-items-center justify-content-center h-100"
            style="background-color: rgba(0, 0, 0, 0.7); border-top-right-radius: 2.5rem; border-bottom-right-radius: 2.5rem;">
            <div class="text-center">
                <h1 v-if="this.loadingstats == 0" class="text-white">Acesse um chat para visualizá-lo.</h1>
                <div v-if="this.loadingstats == 1" class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</template>
