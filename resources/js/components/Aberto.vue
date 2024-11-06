<script>
import { mapState, mapActions } from 'vuex';
import axios from 'axios';

export default {
    data() {
        return {
            chat: [],
            mensagens: [],
            novaMensagem: '',
            mensagensPorChat: {},
            chatSelecionado: null,
            loading: false,
            loadingstats: 0,
            usuarioSelecionado: null,
        }
    },
    computed: {
        ...mapState(['chatsAbertos', 'usuarios', 'mensagensRecebidas']),
        idServidor() {
            return window.idServidor;
        },
        sortedChats() {
            return this.chatsAbertos.slice().sort((a, b) => {
                return new Date(b.ultima_mensagem_em) - new Date(a.ultima_mensagem_em);
            });
        }
    },
    methods: {
        ...mapActions(['fetchChatsAbertos']),
        scrollToBottom() {
            this.$nextTick(() => {
                const chatMessages = this.$refs.chatMessages;
                if (chatMessages) {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            });
        },
        async getMessage(chat) {
            this.chat = chat;
            this.loadingstats = 1;
            const chatIndex = this.sortedChats.findIndex(item => item.id === chat.id);
            if (chatIndex !== -1) {
                // Atualiza visualizado_adm para 0
                this.sortedChats[chatIndex].visualizado_adm = 0; // Atualiza localmente
            }

            try {
                const response = await axios.post('/api/chat/visualizar', {
                    chat_id: chat.id,
                });
                console.log('Mensagem visualizada:', response.data);
            } catch (error) {
                console.error('Erro ao marcar como chat visualizado')
            }

            // Define o usuarioSelecionado com base no chat.usuario_id
            const usuario = this.usuarios.find(usuario => usuario.id === chat.usuario_id);
            this.usuarioSelecionado = usuario;

            // Verifica se já temos mensagens armazenadas para o chat
            if (this.mensagensPorChat[chat.id]) {
                // Se sim, apenas usa as mensagens armazenadas
                this.chatSelecionado = chat.id;
                this.mensagens = this.mensagensPorChat[chat.id];
                this.loadingstats = 0;
                return;
            }

            // Caso contrário, faz a requisição e armazena as mensagens
            try {
                const response = await axios.get('/api/mensagem', {
                    params: { chat_id: chat.id },
                });
                this.mensagensPorChat[chat.id] = response.data; // Armazena as mensagens por chat
                this.mensagens = response.data;
                this.chatSelecionado = chat.id;
            } catch (error) {
                console.error('Erro ao buscar mensagens:', error);
            } finally {
                this.loadingstats = 0;
            }
            this.scrollToBottom();
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

                // Chama a função adicionarMensagem com a nova mensagem enviada
                this.adicionarMensagem(response.data);
            } catch (error) {
                console.error('Erro ao enviar mensagem:', error);
            } finally {
                this.loading = false;
            }
        },

        adicionarMensagem(mensagem) {
            const chatId = mensagem.chat_id;

            // Verifica se já existem mensagens para o chat no estado
            if (this.mensagensPorChat[chatId]) {
                // Adiciona a nova mensagem ao array de mensagens do chat
                this.mensagensPorChat[chatId].push(mensagem);
            } else {
                return;
            }

            if (chatId === this.chatSelecionado) {
                this.scrollToBottom();
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
                });
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

            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const time = `${hours}:${minutes}`;

            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            const formattedDate = `${day}/${month}/${year}`;

            return `${time}, em ${formattedDate}`;
        },
    },
    mounted() {
        this.fetchChatsAbertos();
        console.log('chegou aq');
        const tokenCSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        console.log('Token CSRF:', tokenCSRF);
    },
    watch: {
        mensagensRecebidas(newMensagens) {
            console.log('entrou no new mensagens');

            const ultimaMensagem = newMensagens[newMensagens.length - 1];
            if (ultimaMensagem) {
                console.log('Mensagens por chat atualizadas:', this.mensagensPorChat);

                const chatId = ultimaMensagem.chat_id;
                const chat = this.sortedChats.find(chat => chat.id === chatId);

                if (chat) {
                    console.log('encontrou o chat');
                    chat.ultima_mensagem_em = ultimaMensagem.enviado_em;
                    if(this.chatSelecionado != chatId)
                        chat.visualizado_adm = 1;
                    this.adicionarMensagem(ultimaMensagem);
                }

                // Chama a função adicionarMensagem com a última mensagem recebida
            }

            console.log('Mensagens por chat atualizadas:', this.mensagensPorChat);
        },
    }
};
</script>

<template>
    <!-- <div>
        <h2>Mensagens Recebidas</h2>
        <div v-for="mensagem in mensagensRecebidas" :key="mensagem.id">
            <p>{{ mensagem }}</p>
        </div>
    </div> -->
    <h1 style="margin-right:2.5%; margin-left:5%; margin-top:2.5%" class="lemon-font">Chats abertos</h1>
    <div class="d-flex Box">
        <!-- Lista de Conversas -->
        <div class="col-4"
            style=" height: 100%; overflow-y: auto; background-color: rgba(17, 132, 174, 0.1); border-top-left-radius: 1.5rem; border-bottom-left-radius: 1.5rem;">
            <div>
                <transition-group name="fade" tag="div">
                    <div v-for="chat in sortedChats" :key="chat.id" @click="getMessage(chat)" class="card"
                        style="background-color: rgba(100, 100, 100, 0); width: 100%;">
                        <div class="card-body" style="padding-left: 2rem;">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="card-title">{{ chat.assunto }}</h3>
                                </div>
                                <div class="col-auto">
                                    <span v-if="chat.visualizado_adm === 1"
                                        class="d-flex align-items-center justify-content-center" style="width: 20px; height: 20px; border-radius: 50%; 
                     background-color: #6adae9; color: white; 
                     font-size: 14px;">
                                        &#33;
                                    </span>
                                </div>
                            </div>


                            <p class="card-text">
                                <strong>Criado em:</strong> {{ chat.criado_em }}
                            </p>
                            <p v-if="chat.linha != null" class="card-text">
                                <strong>Linha:</strong> {{ chat.linha }}
                            </p>
                            <p class="card-text mb-4">
                                <strong>Status:</strong> {{ chat.chat_status }}
                            </p>
                            <p class="card-text mb-4">
                                <strong>Última mensagem:</strong> {{ chat.ultima_mensagem_em }}
                            </p>
                            <hr style="margin: 0;">
                        </div>
                    </div>
                </transition-group>
            </div>

        </div>

        <!-- Mensagens do Chat -->
        <div v-if="mensagens.length" class="col-8 d-flex flex-column p-3"
            style=" width: calc(66.66%); background-color: rgba(0, 0, 0, 0.7); border-top-right-radius: 1.5rem; border-bottom-right-radius: 1.5rem;">
            <div class="border-bottom pb-3 mb-3 d-flex flex-row justify-content-between">
                <h3 class="text-white">Assunto: {{ this.chat.assunto }}</h3>
                <button @click="fecharChat()" class="btn btn-danger btn-sm" :disabled="loading">Fechar chat</button>
            </div>

            <div class="d-flex flex-column justify-content-between" style="height: 90%;">
                <div ref="chatMessages" class="chat-messages flex-grow-1 overflow-auto d-flex flex-column mb-3">
                    <div v-for="mensagem in mensagensPorChat[chatSelecionado] || []" :key="mensagem.id"
                        :class="{ 'd-flex justify-content-end': mensagem.admin_id !== null, 'd-flex justify-content-start': mensagem.admin_id === null }"
                        style="margin-bottom: 0.5rem;">
                        <div class="alert alert-dark"
                            style="color: white; display: inline-block; max-width: 70%; word-wrap: break-word; border: none;">
                            <div class="message-content">
                                <div style="color: #6adae9; font-weight: bold;">
                                    {{ mensagem.admin_id !== null ? 'Admin' : (usuarioSelecionado ?
                                        usuarioSelecionado.name : 'Usuário') }}
                                </div>
                                <span>{{ mensagem.mensagem }}</span>
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
                        <i style="rotate: 45deg;" class="fa-solid fa-location-arrow"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Placeholder quando nenhum chat está selecionado -->
        <div v-else class="col-8 d-flex align-items-center justify-content-center h-100"
            style="background-color: rgba(0, 0, 0, 0.7); border-top-right-radius: 1.5rem; border-bottom-right-radius: 1.5rem;">
            <div class="text-center">
                <h1 v-if="this.loadingstats == 0" class="text-white">Acesse um chat para visualizá-lo.</h1>
                <div v-if="this.loadingstats == 1" class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}

.fade-enter,
.fade-leave-to

/* .fade-leave-active em versões <2.1.8 */
    {
    opacity: 0;
}
</style>