<script>
import { mapState, mapActions } from 'vuex';
import axios from 'axios';

export default {
    data() {
        return {
            chat: [],
            mensagens: [],
            novaMensagem: '',
            chatSelecionado: null,
            loading: false,
            loadingstats: 0,
            usuarioSelecionado: null,
        }
    },
    computed: {
        ...mapState(['chats', 'usuarios', 'mensagensRecebidas']),
        idServidor() {
            return window.idServidor;
        },
        sortedChats() {
            return this.chats.slice().sort((a, b) => {
                return new Date(b.ultima_mensagem_em) - new Date(a.ultima_mensagem_em);
            });
        }
    },
    methods: {
        ...mapActions(['fetchChats']),
        async getMessage(chat) {
            this.mensagens = [];
            this.chat = chat;
            this.loadingstats = 1;

            // Define o usuarioSelecionado com base no chat.usuario_id
            const usuario = this.usuarios.find(usuario => usuario.id === chat.usuario_id);
            this.usuarioSelecionado = usuario;

            try {
                const response = await axios.get('/api/mensagem', {
                    params: { chat_id: chat.id },
                });
                this.mensagens = response.data;
                this.chatSelecionado = chat.id;
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
                    admin_id: parseInt(this.idServidor, 10),
                    chat_id: this.chatSelecionado,
                    mensagem: this.novaMensagem,
                });
                this.novaMensagem = '';
                await this.getMessage(this.chat);
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
                });
                this.chatSelecionado = null;
                this.mensagens = [];
                this.loadingstats = 0;
                this.fetchChats();
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
        }
    },
    mounted() {
        this.fetchChats();
    },
    watch: {
        mensagensRecebidas(newMensagens) {

            const ultimaMensagem = newMensagens[newMensagens.length - 1];
            if (ultimaMensagem) {

                const chatId = ultimaMensagem.chat_id;
                const chat = this.sortedChats.find(chat => chat.id === chatId);

                if (chat) {
                    chat.ultima_mensagem_em = ultimaMensagem.enviado_em;
                    if(this.chatSelecionado != chatId)
                        chat.visualizado_adm = 1;
                    this.adicionarMensagem(ultimaMensagem);
                }

                // Chama a função adicionarMensagem com a última mensagem recebida
            }
        },
    }


};
</script>

<template>
    <h1 class="lemon-font" style="margin-right: 2.5%; margin-left: 5%; margin-top: 2.5%">Chats aguardando</h1>
    <div class="d-flex Box">
        <!-- Lista de Conversas -->
        <div class="col-4"
            style="height: 100%; overflow-y: auto; background-color: rgba(17, 132, 174, 0.1); border-top-left-radius: 1.5rem; border-bottom-left-radius: 1.5rem;">

            <div>
                <div v-for="chat in sortedChats" :key="chat.id" @click="getMessage(chat)" class="card"
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
            style="width: calc(66.66%); background-color: rgba(0, 0, 0, 0.7); border-top-right-radius: 1.5rem; border-bottom-right-radius: 1.5rem;">
            <div class="border-bottom pb-3 mb-3 d-flex flex-row justify-content-between">
                <h3 class="text-white">Assunto: {{ this.chat.assunto }}</h3>
                <button @click="fecharChat()" class="btn btn-danger btn-sm" :disabled="loading">
                    Fechar chat
                </button>
            </div>

            <div class="chat-messages flex-grow-1 overflow-auto d-flex flex-column mb-3">
                <div v-for="mensagem in mensagens" :key="mensagem.id"
                    :class="{ 'd-flex justify-content-end': mensagem.admin_id !== null, 'd-flex justify-content-start': mensagem.admin_id === null }"
                    style="margin-bottom: 0.5rem;">
                    <div class="alert alert-dark"
                        style="color: white; display: inline-block; max-width: 70%; word-wrap: break-word; border: none;">
                        <div class="message-content">
                            <!-- Rótulo acima da mensagem -->
                            <div style="color: #6adae9; font-weight: bold;">
                                {{ mensagem.admin_id !== null ? 'Admin' : (usuarioSelecionado ? usuarioSelecionado.name
                                    : 'Usuário') }}
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
                    <i style="rotate: 45deg;" class="fa-solid fa-location-arrow"></i>
                </button>
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
