<script>
import { mapState, mapActions } from 'vuex';
import axios from 'axios';

export default {
    data() {
        return {
            mensagens: [],
            novaMensagem: '',
            chatSelecionado: null,
            loading: false,
            avisoPublicar: false,
            editarMensagens: false,
            publicarStatus: [], // Armazena o estado de publicação para cada mensagem
        }
    },
    computed: {
        ...mapState(['chatsFechados', 'user']),
        idServidor() {
            return window.idServidor;
        }
    },
    methods: {
        ...mapActions(['fetchChatsFechados']),
        async getMessage(chat_id) {
            const response = await axios.get('/api/mensagem', {
                params: {
                    chat_id: chat_id,
                }
            });
            console.log(response);
            this.mensagens = response.data;
            this.chatSelecionado = chat_id;
            this.publicarStatus = this.mensagens.map(() => true); // Predefine como publicável (true) para todas as mensagens
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
        modoPublicarChat() {
            if (!this.chatSelecionado) {
                console.error('Nenhum chat selecionado.');
                return;
            }
            this.editarMensagens = true; // Ativa o modo de edição
            this.avisoPublicar = true;
        },
        async publicarChat() {
            const mensagensPublicaveis = this.mensagens.map((mensagem, index) => {
                const mensagemPublicada = {
                    admin_id: mensagem.admin_id,
                    mensagem: this.publicarStatus[index] ? mensagem.mensagem : '',
                };

                console.log("Mensagem Publicável:", mensagemPublicada);

                return mensagemPublicada;
            });

            try {
                console.log("Mensagens a serem publicadas:", mensagensPublicaveis);

                const response = await axios.post('/api/chat/publicarChat', {
                    chat_id: this.chatSelecionado,
                    mensagens: mensagensPublicaveis,
                });

                console.log('Chat publicado:', response.data);
                this.chatSelecionado = null;
                this.editarMensagens = false; // Desativa o modo de edição após a publicação
                this.avisoPublicar = false;
            } catch (error) {
                console.error('Erro ao publicar chat:', error);
            }
        },

        cancelarPublicarChat() {
            this.avisoPublicar = false;
            this.editarMensagens = false; // Desativa o modo de edição
        }
    },
    mounted() {
        this.fetchChatsFechados();
        this.avisoPublicar = false;
    },
};
</script>

<template>
    <div class="d-flex">
        <!-- Lista de Conversas Fechadas -->
        <div class="col-4 p-4 border-end" style="height: calc(100vh - 3.5rem); background-color: rgba(0, 0, 0, 0.7);">
            <h1 class="mb-4">Chats Fechados</h1>
            <div class="d-flex flex-column justify-content-start gap-3" style="height: 85%; overflow-y: auto;">
                <div v-for="chat in chatsFechados" :key="chat.id" @click="getMessage(chat.id)" class="card" style="background-color: rgba(0, 0, 0, 0.5); width: 95%;">
                    <div class="card-body">
                        <h5 class="card-title">{{ chat.assunto }}</h5>
                        <p class="card-text">
                            <strong>Criado em:</strong> {{ chat.criado_em }}
                        </p>
                        <p v-if="chat.linha != null" class="card-text">
                            <strong>Linha:</strong> {{ chat.linha }}
                        </p>
                        <p class="card-text">
                            <strong>Status:</strong> {{ chat.chat_status }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensagens do Chat Fechado -->
        <div v-if="mensagens.length" class="col-8 d-flex flex-column p-3" style="height: calc(100vh - 3.5rem); width: calc(66.66%); background-color: rgba(0, 0, 0, 0.7);">
            <div class="border-bottom pb-3 mb-3 d-flex flex-row justify-content-between">
                <h3 class="text-white">Chat Fechado</h3>
                <button @click="modoPublicarChat()" class="btn btn-warning btn-sm" :disabled="loading">Tornar chat público</button>
            </div>

            <div class="chat-messages flex-grow-1 overflow-auto d-flex flex-column">
                <div v-for="(mensagem, index) in mensagens" :key="mensagem.id">
                    <div v-if="!editarMensagens" class="alert alert-secondary" style="color: white;">
                        <span>(Admin: {{ mensagem.admin_id }}) diz: {{ mensagem.mensagem }}</span>
                    </div>
                    <div v-else class="alert alert-dark" style="color: white;">
                        <input v-model="mensagem.mensagem" class="form-control mb-2" />
                        <label>
                            Publicar
                            <input type="checkbox" v-model="publicarStatus[index]" checked />
                        </label>
                    </div>
                </div>
            </div>

            <!-- Botões de Publicação -->
            <div v-if="editarMensagens" class="d-flex flex-row justify-content-end mt-3">
                <button @click="cancelarPublicarChat()" class="btn btn-secondary me-2" :disabled="loading">Cancelar</button>
                <button @click="publicarChat()" class="btn btn-success" :disabled="loading">Publicar mensagens</button>
            </div>

            <!-- Input para nova mensagem -->
            <div v-else class="d-flex flex-row justify-content-between mt-3" style="width: 100%;">
                <input class="form-control me-2 my-input" type="text" v-model="novaMensagem" placeholder="Digite sua mensagem">
                <button @click="mandarMensagem()" class="btn btn-success col" :disabled="loading">Enviar mensagem</button>
            </div>
        </div>

        <!-- Placeholder quando nenhum chat está selecionado -->
        <div v-else class="col-8 d-flex align-items-center justify-content-center h-100">
            <h1 class="text-white">Acesse um chat para visualizá-lo.</h1>
        </div>
    </div>
</template>

