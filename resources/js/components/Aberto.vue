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
            abriuChat: false,
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
            this.abriuChat = true;
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
                this.abriuChat = false;
                alert('Ocorreu um problema!')
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
                this.abriuChat = false;
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
    <div class="container mt-5 d-flex">

        <div :class="['dynamic-col', { 'col-12': !abriuChat, 'col-4': abriuChat }]">

            <h1 class="text-white lemon-font" style="margin-left: 4rem;">Chats Abertos</h1>
            <div style="margin-left: 2rem;">
                <p class="text-white m-0">Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                    Lorem Ipsum has been the industry's standard dummy
                </p>
                <p class="text-white">Lorem Ipsum has been the industry's standard dummy</p>
            </div>

            <div class="container">
                <div class="search-bar input-group">
                    <input type="text" class="form-control" placeholder="Pesquisar">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <span class="input-group-text"><i class="fas fa-times"></i></span>
                </div>
            </div>

            <div class="container mt-4 conversation-list d-flex justify-center flex-column"
                style="border-radius: 2rem, 0, 2rem, 0;">
                <div v-for="chat in chatsAbertos " :key="chat.id" class="card border-0 rounded-0 mb-0">
                    <div class="card-body border-bottom" @click="getMessage(chat.id)">
                        <h5 class="card-title">{{ chat.assunto }}</h5>
                        <div v-if="chat.linha != null">{{ chat.linha }}</div>
                        <p class="mb-0" v-if="chat.linha != null">{{ chat.linha }}</p>
                        <p class="mb-0">{{ chat.chat_status }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="mensagens.length" class="col-8 mt-4 px-3 d-flex flex-column" style="height: calc(100vh - 5.5rem);">

            <div class="row justify-content-end">
                <button @click="fecharChat()" class="col-2" :disabled="loading">Fechar chat</button>
            </div>

            <div v-for="mensagem in mensagens" :key="mensagem.id">
                <span>{{ mensagem }}</span>
                <span>CHAT ID: {{ mensagem.chat_id }}</span>
            </div>
            
            <div class="row">
                <input class="col-8" type="text" v-model="novaMensagem">
                <button @click="mandarMensagem()" class="col" :disabled="loading">Enviar mensagem</button>
            </div>

        </div>

    </div>
</template>
