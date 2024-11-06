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
            chatTipo: null,
            chatAssunto: null,
            chatLinha: null,
            loading: false,
            loadingstats: 0,
            avisoPublicar: false,
            editarMensagens: false,
            publicarStatus: [],  // Armazena o status de publicação das mensagens
            usuarioSelecionado: null,
        };
    },
    computed: {
        ...mapState(['chatsFechados', 'usuarios']),
        idServidor() {
            return window.idServidor;
        }
    },
    methods: {
        ...mapActions(['fetchChatsFechados']),
        async getMessage(chat) {
            this.mensagens = [];
            this.chat = chat;
            this.loadingstats = 1;
            // Define o usuarioSelecionado com base no chat.usuario_id
            const usuario = this.usuarios.find(usuario => usuario.id === chat.usuario_id);
            this.usuarioSelecionado = usuario;
            const response = await axios.get('/api/mensagem', {
                params: { chat_id: chat.id }
            });
            this.mensagens = response.data;
            this.chatSelecionado = chat.id;
            this.chatTipo = chat.tipo;
            this.chatAssunto = chat.assunto;
            this.chatLinha = chat.linha;
            this.publicarStatus = this.mensagens.map(() => true);  // Inicializa com todas as mensagens publicáveis

        },
        modoPublicarChat() {
            if (!this.chatSelecionado) {
                console.error('Nenhum chat selecionado.');
                return;
            }
            this.avisoPublicar = true;
            this.editarMensagens = true; // Ativa o modo de edição
        },
        async publicarChat() {
            if (!this.chatSelecionado) {
                console.error('Nenhum chat selecionado.');
                return;
            }

            this.loading = true;

            const mensagensPublicaveis = this.mensagens.map((mensagem, index) => ({
                admin_id: mensagem.admin_id,
                mensagem: mensagem.mensagem,
                publicado: this.publicarStatus[index] ? 1 : 0,
            }));

            try {
                await axios.post('/api/FAQ/publicarChat', {
                    chat_id: this.chatSelecionado,
                    tipo: this.chatTipo,
                    assunto: this.chatAssunto,
                    linha: this.chatLinha,
                    mensagens: mensagensPublicaveis,
                });

                await this.$store.dispatch('fetchFAQ');
            } catch (error) {
                console.error('Erro ao publicar chat:', error);
                this.chatSelecionado = null;
                this.editarMensagens = false;
                this.avisoPublicar = false;
            } finally {
                this.loading = false;
                this.chatSelecionado = null;
                this.editarMensagens = false;
                this.avisoPublicar = false;
            }
        }, cancelarPublicarChat() {
            this.avisoPublicar = false;
            this.editarMensagens = false; // Desativa o modo de edição
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
        this.fetchChatsFechados();
        this.avisoPublicar = false;
    }
};
</script>

<template>
    <h1 style="margin-right:2.5%; margin-left:5%; margin-top:2.5%" class="lemon-font">Chats fechados</h1>
    <div class="d-flex Box">
        <div class="col-4"
            style="height: 100%; overflow-y: auto; background-color: rgba(17, 132, 174, 0.1); border-top-left-radius: 1.5rem; border-bottom-left-radius: 1.5rem;">
            <div v-for="chat in chatsFechados" :key="chat.id" @click="getMessage(chat)" class="card"
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

        <!-- Mensagens do Chat -->
        <div v-if="mensagens.length" class="col-8 d-flex flex-column p-3"
            style="background-color: rgba(0, 0, 0, 0.7); border-top-right-radius: 1.5rem; border-bottom-right-radius: 1.5rem;">
            <div class="border-bottom pb-3 mb-3 d-flex flex-row justify-content-between">
                <h3 class="text-white">Assunto: {{ this.chat.assunto }}</h3>
                <button v-if="!avisoPublicar" @click="modoPublicarChat()" class="btn btn-warning btn-sm"
                    :disabled="loading">Tornar chat público</button>
                <button v-if="avisoPublicar" @click="cancelarPublicarChat()" class="btn btn-secondary btn-sm"
                    :disabled="loading">Cancelar</button>
            </div>

            <div class="d-flex flex-column justify-content-between" style="height: 90%;">
                <div class="chat-messages flex-grow-1 overflow-auto d-flex flex-column mb-3">
                    <div v-for="(mensagem, index) in mensagens" :key="mensagem.id"
                        :class="{ 'd-flex justify-content-end': mensagem.admin_id !== null, 'd-flex justify-content-start': mensagem.admin_id === null }"
                        style="margin-bottom: 0.5rem;">
                        <div class="alert alert-dark"
                            style="color: white; display: inline-block; max-width: 70%; word-wrap: break-word; border: none;">
                            <div class="message-content">
                                <!-- Rótulo acima da mensagem -->
                                <div style="color: #6adae9; font-weight: bold;">
                                    {{ mensagem.admin_id !== null ? 'Admin' : (usuarioSelecionado ?
                                        usuarioSelecionado.name
                                        : 'Usuário') }}
                                </div>
                                <span v-if="!editarMensagens">{{ mensagem.mensagem }}</span>
                                <div v-else>
                                    <input v-model="mensagem.mensagem" class="form-control mb-2" />
                                    <label class="form-check-label">
                                        Publicar
                                        <input type="checkbox" v-model="publicarStatus[index]"
                                            class="form-check-input ms-2" checked />
                                    </label>
                                </div>
                                <!-- Horário no canto inferior direito -->
                                <div style="display: block; text-align: right; color: #AAAAAA; font-size: 12px;">
                                    às {{ formatarData(mensagem.enviado_em) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Controles de Publicação -->
                <div v-if="editarMensagens" class="d-flex flex-row justify-content-between">
                    <button @click="publicarChat()" class="btn btn-primary me-2" :disabled="loading">Publicar
                        mensagens</button>
                    <button @click="cancelarPublicarChat()" class="btn btn-secondary"
                        :disabled="loading">Cancelar</button>
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
